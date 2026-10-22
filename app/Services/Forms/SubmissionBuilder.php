<?php

namespace App\Services\Forms;

use App\Data\ElSalvadorGeography;
use App\Models\FormVersion;
use Illuminate\Validation\ValidationException;

class SubmissionBuilder
{
    private array $errors = [];

    public function build(FormVersion $version, array $values): array
    {
        $this->errors = [];
        $answers = [];

        foreach ($version->fields as $field) {
            if ($field['type'] === 'note') {
                continue;
            }

            [$value, $display] = $this->resolve($field, $values[$field['key']] ?? null);

            $answers[] = [
                'key' => $field['key'],
                'type' => $field['type'],
                'label' => $field['type'] === 'consent' ? ($field['text'] ?? $field['label']) : $field['label'],
                'value' => $value,
                'display' => $display,
            ];
        }

        if ($this->errors !== []) {
            throw ValidationException::withMessages($this->errors);
        }

        $searchText = collect($answers)->pluck('display')->filter()->implode(' ');

        return [
            'answers' => $answers,
            'email' => $this->email($version, $answers),
            'search_text' => mb_substr($searchText, 0, 10000),
        ];
    }

    private function resolve(array $field, mixed $raw): array
    {
        return match ($field['type']) {
            'checkbox_group' => $this->checkboxes($field, $raw),
            'consent' => $this->consent($field, $raw),
            'location' => $this->location($field, $raw),
            default => $this->scalar($field, $raw),
        };
    }

    private function scalar(array $field, mixed $raw): array
    {
        $key = $field['key'];
        $type = $field['type'];
        $text = is_scalar($raw) ? $this->clean((string) $raw, $type === 'textarea') : '';

        if ($text === '') {
            return ($field['required'] ?? false)
                ? $this->fail($key, 'Este campo es obligatorio.')
                : [null, ''];
        }

        switch ($type) {
            case 'text':
            case 'textarea':
                if (mb_strlen($text) > (int) ($field['max_length'] ?? 1000)) {
                    return $this->fail($key, 'El texto es demasiado largo.');
                }
                return [$text, $text];

            case 'proper_name':
                $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
                if (!preg_match('/^\p{L}[\p{L}\p{M} \'’\-]*$/u', $text)) {
                    return $this->fail($key, 'Usa solo letras, espacios, apóstrofe y guion.');
                }
                if (mb_strlen($text) > (int) ($field['max_length'] ?? 150)) {
                    return $this->fail($key, 'El texto es demasiado largo.');
                }
                return [$text, $text];

            case 'dui':
                if (!preg_match('/^\d{9}$/', $text)) {
                    return $this->fail($key, 'El DUI debe tener 9 dígitos, sin guion.');
                }
                if (($field['verify_check_digit'] ?? false) && !$this->duiCheckDigitIsValid($text)) {
                    return $this->fail($key, 'El DUI no es válido.');
                }
                return [$text, $text];

            case 'nit':
                return preg_match('/^\d{14}$/', $text)
                    ? [$text, $text]
                    : $this->fail($key, 'El NIT debe tener 14 dígitos, sin guion.');

            case 'phone_sv':
                return preg_match('/^\d{8}$/', $text)
                    ? [$text, $text]
                    : $this->fail($key, 'El teléfono debe tener 8 dígitos.');

            case 'phone_intl':
                return preg_match('/^\+\d{8,15}$/', $text)
                    ? [$text, $text]
                    : $this->fail($key, 'Escribe el teléfono con código de país, por ejemplo +50370001234.');

            case 'email':
                $text = mb_strtolower($text);
                return (mb_strlen($text) <= 190 && filter_var($text, FILTER_VALIDATE_EMAIL))
                    ? [$text, $text]
                    : $this->fail($key, 'Escribe un correo válido.');

            case 'integer':
                if (!preg_match('/^-?\d{1,15}$/', $text)) {
                    return $this->fail($key, 'Escribe un número entero.');
                }
                if ($message = $this->range($field, (int) $text)) {
                    return $this->fail($key, $message);
                }
                return [$text, $text];

            case 'decimal':
                $decimals = max(1, (int) ($field['decimals'] ?? 2));
                if (!preg_match('/^-?\d{1,15}(\.\d{1,' . $decimals . '})?$/', $text)) {
                    return $this->fail($key, "Escribe un número con hasta {$decimals} decimales.");
                }
                if ($message = $this->range($field, (float) $text)) {
                    return $this->fail($key, $message);
                }
                return [$text, $text];

            case 'select':
                foreach ($field['options'] ?? [] as $option) {
                    if ($option['value'] === $text) {
                        return [$text, $option['label']];
                    }
                }
                return $this->fail($key, 'Elige una opción válida.');
        }

        return $this->fail($key, 'El campo no es válido.');
    }

    private function checkboxes(array $field, mixed $raw): array
    {
        $key = $field['key'];
        $options = $field['options'] ?? [];
        $allowed = array_column($options, 'label', 'value');

        $selected = [];
        foreach (is_array($raw) ? $raw : [] as $item) {
            if (is_scalar($item)) {
                $selected[(string) $item] = true;
            }
        }

        foreach (array_keys($selected) as $value) {
            if (!isset($allowed[$value])) {
                return $this->fail($key, 'Elige solo opciones válidas.');
            }
        }

        if ($selected === []) {
            return ($field['required'] ?? false)
                ? $this->fail($key, 'Elige al menos una opción.')
                : [null, ''];
        }

        $values = [];
        $labels = [];
        foreach ($options as $option) {
            if (isset($selected[$option['value']])) {
                $values[] = $option['value'];
                $labels[] = $option['label'];
            }
        }

        return [$values, implode(', ', $labels)];
    }

    private function consent(array $field, mixed $raw): array
    {
        return filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true
            ? [true, 'Aceptado']
            : $this->fail($field['key'], 'Debes aceptar para continuar.');
    }

    private function location(array $field, mixed $raw): array
    {
        $key = $field['key'];
        $raw = is_array($raw) ? $raw : [];
        $department = is_scalar($raw['department'] ?? null) ? $this->clean((string) $raw['department']) : '';
        $municipality = is_scalar($raw['municipality'] ?? null) ? $this->clean((string) $raw['municipality']) : '';

        if ($department === '' && $municipality === '') {
            return ($field['required'] ?? false)
                ? $this->fail($key, 'Elige departamento y municipio.')
                : [null, ''];
        }

        if (!ElSalvadorGeography::departmentExists($department)) {
            return $this->fail($key, 'Elige un departamento válido.');
        }

        if (!ElSalvadorGeography::municipalityExists($department, $municipality)) {
            return $this->fail($key, 'Elige un municipio válido.');
        }

        return [
            ['department' => $department, 'municipality' => $municipality],
            "{$department} / {$municipality}",
        ];
    }

    private function email(FormVersion $version, array $answers): ?string
    {
        $byKey = collect($answers)->keyBy('key');
        $key = $version->settings['emails']['confirmation']['field_key'] ?? null;

        if ($key && ($byKey->get($key)['value'] ?? null)) {
            return $byKey->get($key)['value'];
        }

        $first = collect($answers)->first(fn ($answer) => $answer['type'] === 'email' && $answer['value']);

        return $first['value'] ?? null;
    }

    private function duiCheckDigitIsValid(string $dui): bool
    {
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $dui[$i] * (9 - $i);
        }

        return ((10 - ($sum % 10)) % 10) === (int) $dui[8];
    }

    private function range(array $field, int|float $number): ?string
    {
        if (isset($field['min']) && $number < $field['min']) {
            return "El valor mínimo es {$field['min']}.";
        }

        if (isset($field['max']) && $number > $field['max']) {
            return "El valor máximo es {$field['max']}.";
        }

        return null;
    }

    private function clean(string $value, bool $multiline = false): string
    {
        $pattern = $multiline ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
        $value = preg_replace($pattern, '', $value) ?? '';

        return trim(str_replace("\r\n", "\n", $value));
    }

    private function fail(string $key, string $message): array
    {
        $this->errors[$key][] = $message;

        return [null, ''];
    }
}