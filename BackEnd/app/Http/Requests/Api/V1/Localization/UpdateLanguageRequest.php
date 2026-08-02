<?php

namespace App\Http\Requests\Api\V1\Localization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'is_active' => ['sometimes', 'required', 'boolean'],
            'is_default' => ['sometimes', 'required', 'boolean'],
            'fallback_locale' => ['sometimes', 'nullable', 'string', 'max:12', Rule::exists('hongvan_languages', 'locale')],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
