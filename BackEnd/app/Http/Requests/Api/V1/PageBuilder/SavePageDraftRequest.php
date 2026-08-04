<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

final class SavePageDraftRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document' => ['required', 'array'],
            'expected_checksum' => ['nullable', 'string', 'size:64'],
            'expected_version_id' => ['nullable', 'string', 'size:26'],
        ];
    }
}
