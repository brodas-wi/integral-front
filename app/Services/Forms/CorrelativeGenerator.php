<?php

namespace App\Services\Forms;

use App\Models\Form;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CorrelativeGenerator
{
    public function next(Form $form, array $settings): ?string
    {
        $config = $settings['correlative'] ?? [];

        if (!($config['enabled'] ?? false) || blank($config['pattern'] ?? null)) {
            return null;
        }

        $now = now();
        $shared = ($config['mode'] ?? 'default') === 'default';
        $scope = $shared ? 'prefix:' . ($config['prefix'] ?? 'SOL') : 'form:' . $form->id;
        $period = $shared ? $now->format('Y') : 'all';

        return $this->render($config['pattern'], $this->increment($scope, $period), $now);
    }

    private function increment(string $scope, string $period): int
    {
        DB::table('form_correlative_counters')->insertOrIgnore([
            'scope' => $scope,
            'period' => $period,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('form_correlative_counters')
            ->where('scope', $scope)
            ->where('period', $period)
            ->lockForUpdate()
            ->first();

        $next = $row->last_number + 1;

        DB::table('form_correlative_counters')
            ->where('id', $row->id)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }

    private function render(string $pattern, int $number, CarbonInterface $date): string
    {
        return preg_replace_callback(
            '/\{(YYYY|YY|MM|DD|N)(?::(\d{1,2}))?\}/',
            fn (array $match) => match ($match[1]) {
                'YYYY' => $date->format('Y'),
                'YY' => $date->format('y'),
                'MM' => $date->format('m'),
                'DD' => $date->format('d'),
                default => str_pad((string) $number, (int) ($match[2] ?? 1), '0', STR_PAD_LEFT),
            },
            $pattern,
        );
    }
}