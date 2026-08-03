<?php

namespace App\Http\Requests\Api\V1\Seo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'source_path' => ['required', 'string', 'max:500'],
            'locale' => ['required', 'string', Rule::in(['*', 'vi', 'en', 'zh'])],
            'target_path' => ['nullable', 'string', 'max:500'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 410])],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
