<?php

namespace App\Http\Requests\Api\V1\Themes;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateThemeDraftRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['tokens' => ['required', 'array']];
    }
}
