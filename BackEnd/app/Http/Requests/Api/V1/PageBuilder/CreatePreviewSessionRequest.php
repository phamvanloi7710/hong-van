<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePreviewSessionRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document' => ['required', 'array'],
            'locale' => ['required', 'string', Rule::in(config('localization.supported_locales', ['vi', 'en', 'zh']))],
        ];
    }
}
