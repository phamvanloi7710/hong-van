<?php

namespace App\Http\Requests\Api\V1\Services;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexServicesRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'array:status,service_type,cta_type,category_id,featured,trashed'],
            'filter.status' => ['nullable', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'filter.service_type' => ['nullable', Rule::in(['general', 'transportation_link', 'warehouse_link'])],
            'filter.cta_type' => ['nullable', Rule::in(['none', 'contact', 'quote'])],
            'filter.category_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_service_categories,public_id'],
            'filter.featured' => ['nullable', 'boolean'],
            'filter.trashed' => ['nullable', Rule::in(['without', 'with', 'only'])],
            'sort' => ['nullable', Rule::in(['code', '-code', 'sort_order', '-sort_order', 'status', '-status', 'published_at', '-published_at', 'updated_at', '-updated_at'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
