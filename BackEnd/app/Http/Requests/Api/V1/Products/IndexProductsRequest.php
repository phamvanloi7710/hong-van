<?php

namespace App\Http\Requests\Api\V1\Products;

use Illuminate\Foundation\Http\FormRequest;

final class IndexProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'filter' => ['sometimes', 'array:status,category_id,brand_id,price_mode,featured,trashed'],
            'filter.status' => ['sometimes', 'string', 'in:draft,published,archived,scheduled'],
            'filter.category_id' => ['sometimes', 'ulid', 'exists:hongvan_product_categories,public_id'],
            'filter.brand_id' => ['sometimes', 'ulid', 'exists:hongvan_brands,public_id'],
            'filter.price_mode' => ['sometimes', 'string', 'in:fixed,from,range,market,dealer,quantity,contact'],
            'filter.featured' => ['sometimes', 'boolean'],
            'filter.trashed' => ['sometimes', 'string', 'in:without,with,only'],
            'sort' => ['sometimes', 'string', 'in:created_at,-created_at,updated_at,-updated_at,sku,-sku,published_at,-published_at'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
