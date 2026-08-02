<?php

namespace App\Http\Requests\Api\V1\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class IndexAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array:action,actor_public_id,subject_type,subject_public_id,request_id,date_from,date_to'],
            'filter.action' => ['sometimes', 'string', 'max:191'],
            'filter.actor_public_id' => ['sometimes', 'ulid'],
            'filter.subject_type' => ['sometimes', 'string', 'regex:/^[a-z][a-z0-9_.-]*$/', 'max:100'],
            'filter.subject_public_id' => ['sometimes', 'ulid'],
            'filter.request_id' => ['sometimes', 'ulid'],
            'filter.date_from' => ['sometimes', 'date_format:Y-m-d'],
            'filter.date_to' => ['sometimes', 'date_format:Y-m-d'],
            'sort' => ['sometimes', 'string', 'in:occurred_at,-occurred_at,action,-action'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
