<?php

namespace App\Http\Controllers;

use App\Data\ElSalvadorGeography;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormVersion;
use App\Services\Forms\SubmissionBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $version = $this->activeForm($slug)->currentVersion;

        return response()->json([
            'slug' => $slug,
            'version_id' => $version->id,
            'title' => $version->title,
            'description' => $version->description,
            'submit_label' => $version->submit_label,
            'fields' => $version->fields,
            'thanks' => $version->settings['thanks'] ?? [],
        ])->header('Cache-Control', 'no-cache, must-revalidate');
    }

    public function submit(Request $request, string $slug, SubmissionBuilder $builder): JsonResponse
    {
        abort_if((int) $request->header('Content-Length') > 65536, 413, 'La solicitud es demasiado grande.');

        $form = $this->activeForm($slug);

        $data = $request->validate([
            'values' => ['required', 'array'],
            'version_id' => ['nullable', 'integer'],
            'website' => ['nullable', 'string', 'max:200'],
            'elapsed_ms' => ['nullable', 'integer', 'min:0'],
            'source_url' => ['nullable', 'string', 'max:2048'],
        ]);

        if (filled($data['website'] ?? null) || ($data['elapsed_ms'] ?? 5000) < 2500) {
            return response()->json(['success' => true], 201);
        }

        $version = $form->currentVersion;

        if (!empty($data['version_id'])) {
            $version = FormVersion::where('form_id', $form->id)->find($data['version_id']) ?? $version;
        }

        $built = $builder->build($version, $data['values']);

        $emails = $version->settings['emails'] ?? [];
        $notify = ($emails['notify']['enabled'] ?? false) && !empty($emails['notify']['recipients']);
        $confirm = ($emails['confirmation']['enabled'] ?? false) && $built['email'];

        $sourceUrl = $data['source_url'] ?? null;

        FormSubmission::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $form->id,
            'form_version_id' => $version->id,
            'answers' => $built['answers'],
            'email' => $built['email'],
            'search_text' => $built['search_text'],
            'status' => 'new',
            'source_url' => $sourceUrl && preg_match('/^https?:\/\//i', $sourceUrl) ? $sourceUrl : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            'notify_status' => $notify ? 'pending' : 'not_applicable',
            'confirmation_status' => $confirm ? 'pending' : 'not_applicable',
        ]);

        return response()->json(['success' => true], 201);
    }

    public function geography(): JsonResponse
    {
        $map = collect(ElSalvadorGeography::departments())
            ->mapWithKeys(fn (string $department) => [$department => ElSalvadorGeography::municipalities($department)]);

        return response()->json($map)->header('Cache-Control', 'public, max-age=86400');
    }

    private function activeForm(string $slug): Form
    {
        $form = Form::active()->where('slug', $slug)->with('currentVersion')->first();

        abort_if(!$form || !$form->currentVersion, 404, 'Formulario no disponible.');

        return $form;
    }
}