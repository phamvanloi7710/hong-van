<?php

namespace App\Http\Requests\Api\V1\Posts;

use App\Models\Post;
use App\Models\PostSlugHistory;
use App\Models\PostTranslation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SavePostRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $post = $this->route('post');
        $postId = $post instanceof Post ? $post->getKey() : null;

        return [
            'category_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_post_categories,public_id'],
            'author_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_users,public_id'],
            'featured_media_id' => ['nullable', 'string', 'size:26', 'exists:hongvan_media,public_id'],
            'tag_ids' => ['nullable', 'array', 'max:30'],
            'tag_ids.*' => ['required', 'distinct', 'string', 'size:26', 'exists:hongvan_post_tags,public_id'],
            'code' => ['required', 'string', 'max:100', Rule::unique('hongvan_posts', 'code')->ignore($postId)],
            'status' => ['required', Rule::in(Post::STATUSES)],
            'is_featured' => ['required', 'boolean'],
            'scheduled_for' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'unpublished_at' => ['nullable', 'date', 'after:published_at'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                static function (string $attribute, mixed $value, \Closure $fail) use ($postId): void {
                    $locale = request()->input(str_replace('.slug', '.locale', $attribute));
                    $existsCurrent = PostTranslation::query()->where('locale', $locale)->where('slug', $value)
                        ->when($postId, static fn ($query) => $query->where('post_id', '!=', $postId))->exists();
                    $existsHistory = PostSlugHistory::query()->where('locale', $locale)->where('slug', $value)
                        ->when($postId, static fn ($query) => $query->where('post_id', '!=', $postId))->exists();
                    if ($existsCurrent || $existsHistory) {
                        $fail(__('posts.slug_taken'));
                    }
                },
            ],
            'translations.*.excerpt' => ['nullable', 'string', 'max:2000'],
            'translations.*.content_html' => ['required', 'string', 'max:200000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('status') === 'scheduled' && blank($this->input('scheduled_for'))) {
                $validator->errors()->add('scheduled_for', __('posts.scheduled_requires_date'));
            }
            if ($this->input('status') !== 'scheduled' && filled($this->input('scheduled_for'))) {
                $validator->errors()->add('scheduled_for', __('posts.schedule_only_for_scheduled'));
            }
        }];
    }
}
