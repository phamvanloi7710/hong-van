<?php

namespace App\Http\Requests\Api\V1\Services;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveServiceRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $service = $this->route('service');
        $serviceId = $service instanceof Service ? $service->getKey() : null;

        return [
            'category_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_service_categories,public_id'],
            'code' => ['required', 'string', 'max:100', Rule::unique('hongvan_services', 'code')->ignore($serviceId)],
            'service_type' => ['required', Rule::in(['general', 'transportation_link', 'warehouse_link'])],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'cta_type' => ['required', Rule::in(['none', 'contact', 'quote'])],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'published_at' => ['nullable', 'date'],
            'unpublished_at' => ['nullable', 'date', 'after:published_at'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.content' => ['nullable', 'string', 'max:50000'],
            'translations.*.content_sections' => ['nullable', 'array', 'max:20'],
            'translations.*.content_sections.*' => ['required', 'array:title,body'],
            'translations.*.content_sections.*.title' => ['required', 'string', 'max:255'],
            'translations.*.content_sections.*.body' => ['required', 'string', 'max:10000'],
            'translations.*.cta_label' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:2000'],
            'media' => ['nullable', 'array', 'max:50'],
            'media.*.media_id' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'media.*.role' => ['required', Rule::in(['hero', 'gallery', 'document'])],
            'media.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('status') === 'scheduled' && blank($this->input('published_at'))) {
                $validator->errors()->add('published_at', __('services.scheduled_requires_date'));
            }
            if ($this->input('service_type') !== 'general') {
                if ($this->input('cta_type') !== 'none') {
                    $validator->errors()->add('cta_type', __('services.specialized_cta_forbidden'));
                }
                if ($this->input('media', []) !== []) {
                    $validator->errors()->add('media', __('services.specialized_media_forbidden'));
                }
                foreach ((array) $this->input('translations', []) as $index => $translation) {
                    if (filled(data_get($translation, 'content')) || data_get($translation, 'content_sections', []) !== []) {
                        $validator->errors()->add("translations.$index.content", __('services.specialized_content_forbidden'));
                    }
                }
            }
        }];
    }
}
