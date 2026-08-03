<?php

namespace App\Http\Requests\Api\V1\Showcase;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveShowcaseRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $kind = (string) $this->route('kind');
        $table = match ($kind) {
            'galleries' => 'hongvan_galleries',
            'gallery-items' => 'hongvan_gallery_items',
            'partners' => 'hongvan_partners',
            'certifications' => 'hongvan_certifications',
            'projects' => 'hongvan_projects',
            default => 'hongvan_galleries',
        };
        $id = $this->route('publicId');
        $modelId = is_string($id) ? (int) DB::table($table)->where('public_id', $id)->value('id') : null;
        $translation = match ($kind) {
            'galleries' => ['hongvan_gallery_translations', 'gallery_id'],
            'certifications' => ['hongvan_certification_translations', 'certification_id'],
            'projects' => ['hongvan_project_translations', 'project_id'],
            default => [null, null],
        };
        $codeRules = $kind === 'gallery-items'
            ? ['prohibited']
            : ['required', 'string', 'max:100', Rule::unique($table, 'code')->ignore($modelId)];

        return [
            'code' => $codeRules,
            'gallery_id' => [Rule::requiredIf($kind === 'gallery-items'), 'nullable', 'string', 'size:26', 'exists:hongvan_galleries,public_id'],
            'media_id' => [Rule::requiredIf($kind === 'gallery-items'), 'nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'logo_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'image_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'document_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'document_visibility' => [Rule::requiredIf($kind === 'certifications'), 'nullable', Rule::in(['private', 'public'])],
            'website_url' => ['nullable', 'url:http,https', 'max:2048'],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'started_on' => ['nullable', 'date'],
            'completed_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.name' => [Rule::requiredIf(in_array($kind, ['galleries', 'partners', 'certifications'], true)), 'nullable', 'string', 'max:255'],
            'translations.*.title' => [Rule::requiredIf($kind === 'projects'), 'nullable', 'string', 'max:255'],
            'translations.*.slug' => [Rule::requiredIf(in_array($kind, ['galleries', 'certifications', 'projects'], true)), 'nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', function (string $attribute, mixed $value, \Closure $fail) use ($translation, $modelId): void {
                if (! is_string($translation[0])) {
                    return;
                }
                $locale = request()->input(str_replace('.slug', '.locale', $attribute));
                if (DB::table($translation[0])->where('locale', $locale)->where('slug', $value)->when($modelId, fn ($query) => $query->where($translation[1], '!=', $modelId))->exists()) {
                    $fail(__('validation.unique'));
                }
            }],
            'translations.*.description' => ['nullable', 'string', 'max:20000'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.content' => ['nullable', 'string', 'max:100000'],
            'translations.*.location' => ['nullable', 'string', 'max:255'],
            'translations.*.issuer' => ['nullable', 'string', 'max:255'],
            'translations.*.alt_text' => [Rule::requiredIf($kind === 'gallery-items'), 'nullable', 'string', 'max:255'],
            'translations.*.logo_alt' => [Rule::requiredIf($kind === 'partners' && filled($this->input('logo_media_id'))), 'nullable', 'string', 'max:255'],
            'translations.*.image_alt' => ['nullable', 'string', 'max:255'],
            'translations.*.caption' => ['nullable', 'string', 'max:2000'],
            'translations.*.document_label' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:2000'],
            'media_items' => ['nullable', 'array', 'max:50'],
            'media_items.*.public_id' => ['nullable', 'string', 'size:26'],
            'media_items.*.media_id' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'media_items.*.role' => ['required', Rule::in(['cover', 'gallery', 'document'])],
            'media_items.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'media_items.*.translations' => ['required', 'array', 'size:3'],
            'media_items.*.translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'media_items.*.translations.*.alt_text' => ['required', 'string', 'max:255'],
            'media_items.*.translations.*.caption' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (filled($this->input('media_id')) && ! $this->isReadyMedia((string) $this->input('media_id'), ['image/', 'video/'])) {
                $validator->errors()->add('media_id', __('showcase.media_invalid'));
            }
            foreach (['logo_media_id', 'image_media_id'] as $field) {
                if (filled($this->input($field)) && ! $this->isReadyMedia((string) $this->input($field), ['image/'])) {
                    $validator->errors()->add($field, __('showcase.media_invalid'));
                }
            }
            if (filled($this->input('document_media_id')) && ! $this->isReadyMedia((string) $this->input('document_media_id'), ['application/pdf'])) {
                $validator->errors()->add('document_media_id', __('showcase.document_invalid'));
            }
            foreach ((array) $this->input('media_items', []) as $index => $item) {
                $allowed = is_array($item) && ($item['role'] ?? null) === 'document' ? ['application/pdf'] : ['image/', 'video/'];
                if (is_array($item) && isset($item['media_id']) && ! $this->isReadyMedia((string) $item['media_id'], $allowed)) {
                    $validator->errors()->add("media_items.{$index}.media_id", __('showcase.media_invalid'));
                }
            }
        }];
    }

    /** @param list<string> $allowed */
    private function isReadyMedia(string $publicId, array $allowed): bool
    {
        $media = Media::query()->where('public_id', $publicId)->where('status', 'ready')->first();
        if ($media === null) {
            return false;
        }
        foreach ($allowed as $prefix) {
            if (str_starts_with($media->mime_type, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
