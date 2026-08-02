<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
            'filter' => ['sometimes', 'array:status,mime_type,folder_id,tag,trashed,visibility,locked'],
            'filter.status' => ['sometimes', 'string', 'in:processing,ready,failed,trashed'],
            'filter.mime_type' => ['sometimes', 'string', 'max:127'],
            'filter.folder_id' => ['sometimes', 'string', static function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value !== 'root' && (! is_string($value) || ! Str::isUlid($value))) {
                    $fail(__('validation.ulid', ['attribute' => $attribute]));
                }
            }],
            'filter.tag' => ['sometimes', 'alpha_dash:ascii', 'max:191'],
            'filter.trashed' => ['sometimes', 'string', 'in:without,with,only'],
            'filter.visibility' => ['sometimes', 'string', 'in:private,public'],
            'filter.locked' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'string', 'in:created_at,-created_at,updated_at,-updated_at,original_filename,-original_filename,size_bytes,-size_bytes'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
