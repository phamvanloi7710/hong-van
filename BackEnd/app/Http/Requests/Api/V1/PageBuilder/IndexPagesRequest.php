<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexPagesRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'array:status,type'],
            'filter.status' => ['nullable', Rule::in(Page::STATUSES)],
            'filter.type' => ['nullable', Rule::in(Page::TYPES)],
            'sort' => ['nullable', Rule::in(['code', '-code', 'type', '-type', 'status', '-status', 'updated_at', '-updated_at'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
