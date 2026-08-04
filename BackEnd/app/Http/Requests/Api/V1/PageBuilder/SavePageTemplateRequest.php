<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavePageTemplateRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_.-]*$/', Rule::unique('hongvan_page_templates', 'key')],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_key' => ['nullable', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_-]*$/'],
        ];
    }
}
