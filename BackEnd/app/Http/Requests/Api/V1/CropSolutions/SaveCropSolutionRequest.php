<?php

namespace App\Http\Requests\Api\V1\CropSolutions;

use App\Models\CropSolution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveCropSolutionRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $solution = $this->route('solution');
        $solutionId = $solution instanceof CropSolution ? $solution->getKey() : null;

        return [
            'crop_id' => ['required', 'string', 'size:26', 'exists:hongvan_crops,public_id'],
            'stage_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_crop_stages,public_id'],
            'code' => ['required', 'string', 'max:100', Rule::unique('hongvan_crop_solutions', 'code')->ignore($solutionId)],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'hero_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'published_at' => ['nullable', 'date'],
            'unpublished_at' => ['nullable', 'date', 'after:published_at'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.content' => ['nullable', 'string', 'max:50000'],
            'translations.*.content_sections' => ['nullable', 'array', 'max:20'],
            'translations.*.content_sections.*' => ['required', 'array:title,body'],
            'translations.*.content_sections.*.title' => ['required', 'string', 'max:255'],
            'translations.*.content_sections.*.body' => ['required', 'string', 'max:10000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:2000'],
            'products' => ['nullable', 'array', 'max:50'],
            'products.*.product_id' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_products,public_id'],
            'products.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'products.*.recommendation_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('status') === 'scheduled' && blank($this->input('published_at'))) {
                $validator->errors()->add('published_at', __('crop_solutions.scheduled_requires_date'));
            }
        }];
    }
}
