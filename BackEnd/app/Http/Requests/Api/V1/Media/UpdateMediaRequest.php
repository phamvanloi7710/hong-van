<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['present', 'nullable', 'string', 'max:255'],
            'alt_text' => ['present', 'nullable', 'string', 'max:255'],
            'caption' => ['present', 'nullable', 'string', 'max:4000'],
            'tag_ids' => ['sometimes', 'array', 'max:50'],
            'tag_ids.*' => ['required', 'ulid', 'distinct', 'exists:hongvan_media_tags,public_id'],
        ];
    }
}
