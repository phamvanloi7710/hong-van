<?php

namespace App\Http\Requests\Api\V1\Products;

use App\Models\ProductTag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveProductTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tag = $this->route('tag');
        $tagId = $tag instanceof ProductTag ? $tag->getKey() : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('hongvan_product_tags', 'slug')->ignore($tagId)],
        ];
    }
}
