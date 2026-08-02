<?php

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class SaveSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'alpha_dash:ascii', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:https', 'max:2000'],
            'icon' => ['nullable', 'alpha_dash:ascii', 'max:64'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'between:0,65535'],
        ];
    }
}
