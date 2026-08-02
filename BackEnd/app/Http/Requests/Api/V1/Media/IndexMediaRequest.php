<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

final class IndexMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'filter' => ['sometimes', 'array:status,mime_type,folder_id,tag,trashed'],
            'filter.status' => ['sometimes', 'string', 'in:processing,ready,failed,trashed'],
            'filter.mime_type' => ['sometimes', 'string', 'max:127'],
            'filter.folder_id' => ['sometimes', 'ulid'],
            'filter.tag' => ['sometimes', 'alpha_dash:ascii', 'max:191'],
            'filter.trashed' => ['sometimes', 'string', 'in:without,with,only'],
            'sort' => ['sometimes', 'string', 'in:created_at,-created_at,original_filename,-original_filename,size_bytes,-size_bytes'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
