<?php

namespace App\Http\Requests\Api\V1\Search;

use Illuminate\Foundation\Http\FormRequest;

final class RelatedContentRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['limit' => ['sometimes', 'integer', 'min:1', 'max:12']];
    }
}
