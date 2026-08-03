<?php

namespace App\Http\Requests\Api\V1\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexPostsRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'array:status,category_id,featured,trashed'],
            'filter.status' => ['nullable', Rule::in(['draft', 'scheduled', 'published', 'archived'])],
            'filter.category_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_post_categories,public_id'],
            'filter.featured' => ['nullable', 'boolean'],
            'filter.trashed' => ['nullable', Rule::in(['without', 'with', 'only'])],
            'sort' => ['nullable', Rule::in(['code', '-code', 'status', '-status', 'scheduled_for', '-scheduled_for', 'published_at', '-published_at', 'updated_at', '-updated_at'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
