<?php

namespace App\Http\Requests\Api\V1\Seo;

use App\Models\Media;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::in(['vi', 'en', 'zh'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:2048'],
            'robots_index' => ['required', 'boolean'],
            'robots_follow' => ['required', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image_media_id' => ['nullable', 'ulid', $this->readyPublicImage(...)],
            'og_type' => ['required', 'string', Rule::in(['website', 'article', 'product'])],
            'twitter_card' => ['required', 'string', Rule::in(['summary', 'summary_large_image'])],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:500'],
            'focus_keywords' => ['nullable', 'array', 'max:10'],
            'focus_keywords.*' => ['required', 'string', 'max:80', 'distinct'],
        ];
    }

    private function readyPublicImage(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $valid = Media::query()
            ->where('public_id', $value)
            ->where('status', 'ready')
            ->where('visibility', 'public')
            ->where('mime_type', 'like', 'image/%')
            ->exists();

        if (! $valid) {
            $fail('The selected Open Graph image must be a ready public image.');
        }
    }
}
