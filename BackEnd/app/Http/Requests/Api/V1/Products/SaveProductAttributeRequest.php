<?php

namespace App\Http\Requests\Api\V1\Products;

use App\Models\ProductAttributeDefinition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $attribute = $this->route('attribute');
        $attributeId = $attribute instanceof ProductAttributeDefinition ? $attribute->getKey() : null;

        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('hongvan_product_attribute_definitions', 'code')->ignore($attributeId)],
            'name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'string', 'in:text,decimal,boolean,option,json'],
            'unit' => ['nullable', 'string', 'max:64'],
            'options' => ['nullable', 'array', 'max:100'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
            'is_filterable' => ['required', 'boolean'],
            'is_required' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
