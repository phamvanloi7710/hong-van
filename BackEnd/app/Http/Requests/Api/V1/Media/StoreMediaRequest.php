<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $max = max(1, (int) config('media.max_upload_kb', 10240));

        return [
            'file' => ['required', 'file', 'max:'.$max, 'extensions:jpg,jpeg,png,gif,webp,avif,pdf'],
            'folder_id' => ['nullable', 'ulid', 'exists:hongvan_media_folders,public_id'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
