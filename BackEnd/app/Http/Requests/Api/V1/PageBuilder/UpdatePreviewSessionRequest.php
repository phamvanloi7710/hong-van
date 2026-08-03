<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePreviewSessionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['token' => $this->header('X-Preview-Token')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document' => ['required', 'array'],
            'token' => ['required', 'string', 'size:64', 'regex:/\A[A-Za-z0-9]+\z/'],
        ];
    }
}
