<?php

namespace App\Http\Requests\Api\V1\Media;

use Illuminate\Foundation\Http\FormRequest;

final class ShowMediaContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['variant' => ['sometimes', 'string', 'regex:/^[a-z0-9_]{1,64}$/']];
    }
}
