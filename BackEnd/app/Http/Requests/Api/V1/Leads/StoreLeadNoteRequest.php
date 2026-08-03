<?php

namespace App\Http\Requests\Api\V1\Leads;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLeadNoteRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:10000']];
    }
}
