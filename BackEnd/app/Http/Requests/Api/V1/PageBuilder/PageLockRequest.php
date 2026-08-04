<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use Illuminate\Foundation\Http\FormRequest;

final class PageLockRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['token' => ['required', 'string', 'size:64']];
    }
}
