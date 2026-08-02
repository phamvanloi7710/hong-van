<?php

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class SaveContactChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'alpha_dash:ascii', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'href' => ['nullable', 'string', 'max:2000', 'regex:/^(https:\/\/|mailto:|tel:)/i'],
            'availability_note' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'between:0,65535'],
        ];
    }
}
