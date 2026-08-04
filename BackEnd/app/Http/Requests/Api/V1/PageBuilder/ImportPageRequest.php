<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ImportPageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_.-]*$/', Rule::unique('hongvan_pages', 'code')],
            'type' => ['required', Rule::in(Page::TYPES)],
            'is_home' => ['required', 'boolean'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.navigation_label' => ['nullable', 'string', 'max:160'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'payload' => ['required', 'array'],
            'media_map' => ['nullable', 'array', 'max:200'],
            'media_map.*' => ['required', 'string', 'size:26'],
        ];
    }
}
