<?php

namespace App\Http\Requests\Api\V1\Posts;

use App\Models\PostTag;
use App\Models\PostTagTranslation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavePostTagRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tag = $this->route('tag');
        $id = $tag instanceof PostTag ? $tag->getKey() : null;

        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('hongvan_post_tags', 'code')->ignore($id)],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                static function (string $attribute, mixed $value, \Closure $fail) use ($id): void {
                    $locale = request()->input(str_replace('.slug', '.locale', $attribute));
                    $exists = PostTagTranslation::query()->where('locale', $locale)->where('slug', $value)
                        ->when($id, static fn ($query) => $query->where('post_tag_id', '!=', $id))->exists();
                    if ($exists) {
                        $fail(__('posts.slug_taken'));
                    }
                },
            ],
        ];
    }
}
