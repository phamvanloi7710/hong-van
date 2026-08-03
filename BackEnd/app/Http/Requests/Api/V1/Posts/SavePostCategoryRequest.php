<?php

namespace App\Http\Requests\Api\V1\Posts;

use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavePostCategoryRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $category = $this->route('category');
        $id = $category instanceof PostCategory ? $category->getKey() : null;

        return [
            'parent_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_post_categories,public_id'],
            'code' => ['required', 'string', 'max:64', Rule::unique('hongvan_post_categories', 'code')->ignore($id)],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                static function (string $attribute, mixed $value, \Closure $fail) use ($id): void {
                    $locale = request()->input(str_replace('.slug', '.locale', $attribute));
                    $exists = PostCategoryTranslation::query()->where('locale', $locale)->where('slug', $value)
                        ->when($id, static fn ($query) => $query->where('post_category_id', '!=', $id))->exists();
                    if ($exists) {
                        $fail(__('posts.slug_taken'));
                    }
                },
            ],
            'translations.*.description' => ['nullable', 'string', 'max:5000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
