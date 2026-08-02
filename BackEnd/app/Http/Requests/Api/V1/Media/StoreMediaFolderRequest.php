<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMediaFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'ulid', 'exists:hongvan_media_folders,public_id'],
            'sort_order' => ['sometimes', 'integer', 'between:0,65535'],
        ];
    }
}
