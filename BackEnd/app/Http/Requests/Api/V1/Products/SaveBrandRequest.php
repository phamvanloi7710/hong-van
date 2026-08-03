<?php

namespace App\Http\Requests\Api\V1\Products;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = $brand instanceof Brand ? $brand->getKey() : null;

        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('hongvan_brands', 'code')->ignore($brandId)],
            'logo_media_id' => ['nullable', 'ulid', 'exists:hongvan_media,public_id'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'min:1', 'max:3'],
            'translations.*' => ['required', 'array:locale,name,slug,description,meta_title,meta_description'],
            'translations.*.locale' => ['required', 'string', 'distinct', 'in:vi,en,zh'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'translations.*.description' => ['nullable', 'string', 'max:10000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:4000'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $brand = $this->route('brand');
            foreach ((array) $this->input('translations', []) as $index => $translation) {
                if (! is_array($translation) || ! is_string($translation['locale'] ?? null) || ! is_string($translation['slug'] ?? null)) {
                    continue;
                }
                $exists = DB::table('hongvan_brand_translations')
                    ->where('locale', $translation['locale'])
                    ->where('slug', $translation['slug'])
                    ->when($brand instanceof Brand, static fn ($query) => $query->where('brand_id', '!=', $brand->getKey()))
                    ->exists();
                if ($exists) {
                    $validator->errors()->add("translations.$index.slug", __('products.slug_taken'));
                }
            }
        }];
    }
}
