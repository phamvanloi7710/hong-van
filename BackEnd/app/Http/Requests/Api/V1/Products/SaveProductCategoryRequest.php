<?php

namespace App\Http\Requests\Api\V1\Products;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof ProductCategory ? $category->getKey() : null;

        return [
            'parent_id' => ['nullable', 'ulid', 'exists:hongvan_product_categories,public_id'],
            'code' => ['required', 'string', 'max:64', Rule::unique('hongvan_product_categories', 'code')->ignore($categoryId)],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'min:1', 'max:3'],
            'translations.*' => ['required', 'array:locale,name,slug,summary,meta_title,meta_description'],
            'translations.*.locale' => ['required', 'string', 'distinct', 'in:vi,en,zh'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.summary' => ['nullable', 'string', 'max:4000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:4000'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $category = $this->route('category');
            if ($category instanceof ProductCategory && $this->input('parent_id') === $category->public_id) {
                $validator->errors()->add('parent_id', __('products.category_parent_self'));
            }

            foreach ((array) $this->input('translations', []) as $index => $translation) {
                if (! is_array($translation) || ! is_string($translation['locale'] ?? null) || ! is_string($translation['slug'] ?? null)) {
                    continue;
                }
                $exists = DB::table('hongvan_product_category_translations')
                    ->where('locale', $translation['locale'])
                    ->where('slug', $translation['slug'])
                    ->when($category instanceof ProductCategory, static fn ($query) => $query->where('product_category_id', '!=', $category->getKey()))
                    ->exists();
                if ($exists) {
                    $validator->errors()->add("translations.$index.slug", __('products.slug_taken'));
                }
            }
        }];
    }
}
