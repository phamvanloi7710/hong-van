<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

final class MoveMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['folder_id' => ['present', 'nullable', 'ulid', 'exists:hongvan_media_folders,public_id']];
    }
}
