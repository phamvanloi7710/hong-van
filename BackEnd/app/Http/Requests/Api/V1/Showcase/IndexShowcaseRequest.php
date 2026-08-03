<?php

namespace App\Http\Requests\Api\V1\Showcase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexShowcaseRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'array:status,featured,trashed,gallery_id'],
            'filter.status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'filter.featured' => ['nullable', 'boolean'],
            'filter.trashed' => ['nullable', Rule::in(['without', 'with', 'only'])],
            'filter.gallery_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_galleries,public_id'],
            'sort' => ['nullable', Rule::in(['code', '-code', 'status', '-status', 'sort_order', '-sort_order', 'updated_at', '-updated_at'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
