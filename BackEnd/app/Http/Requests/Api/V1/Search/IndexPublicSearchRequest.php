<?php

namespace App\Http\Requests\Api\V1\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class IndexPublicSearchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('q'))) {
            $this->merge(['q' => Str::squish($this->input('q'))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:'.config('search.min_query_length', 2), 'max:'.config('search.max_query_length', 100)],
            'types' => ['sometimes', 'array', 'max:'.count(config('search.types', []))],
            'types.*' => ['string', 'distinct', Rule::in(config('search.types', []))],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('search.max_per_page', 24)],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
