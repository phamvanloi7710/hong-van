<?php

namespace App\Http\Requests\Api\V1\Seo;

use App\Domain\Seo\SeoEntityRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexSeoEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(app(SeoEntityRegistry::class)->types())],
            'locale' => ['required', 'string', Rule::in(['vi', 'en', 'zh'])],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
