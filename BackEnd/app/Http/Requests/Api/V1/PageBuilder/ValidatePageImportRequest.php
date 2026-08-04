<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

final class ValidatePageImportRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],
            'media_map' => ['nullable', 'array', 'max:200'],
            'media_map.*' => ['required', 'string', 'size:26'],
        ];
    }
}
