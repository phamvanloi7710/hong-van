<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

final class PublishPageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'expected_checksum' => ['required', 'string', 'size:64'],
            'expected_version_id' => ['required', 'string', 'size:26'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
