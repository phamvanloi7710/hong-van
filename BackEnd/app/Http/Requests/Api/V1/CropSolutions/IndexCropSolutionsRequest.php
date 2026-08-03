<?php

namespace App\Http\Requests\Api\V1\CropSolutions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexCropSolutionsRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:191'],
            'filter' => ['nullable', 'array:status,crop_id,stage_id,featured,trashed'],
            'filter.status' => ['nullable', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'filter.crop_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_crops,public_id'],
            'filter.stage_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_crop_stages,public_id'],
            'filter.featured' => ['nullable', 'boolean'],
            'filter.trashed' => ['nullable', Rule::in(['without', 'with', 'only'])],
            'sort' => ['nullable', Rule::in(['code', '-code', 'sort_order', '-sort_order', 'published_at', '-published_at', 'updated_at', '-updated_at'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
