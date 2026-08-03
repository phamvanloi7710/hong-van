<?php

namespace App\Http\Requests\Api\V1\PageBuilder;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavePageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $page = $this->route('page');
        $pageId = $page instanceof Page ? $page->getKey() : null;

        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_.-]*$/', Rule::unique('hongvan_pages', 'code')->ignore($pageId)],
            'type' => ['required', Rule::in(Page::TYPES)],
            'is_home' => ['required', 'boolean'],
            'translations' => ['required', 'array', 'size:3'],
            'translations.*.locale' => ['required', 'distinct', Rule::in(['vi', 'en', 'zh'])],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.navigation_label' => ['nullable', 'string', 'max:160'],
            'translations.*.slug' => [
                'required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                static function (string $attribute, mixed $value, \Closure $fail) use ($pageId): void {
                    $locale = request()->input(str_replace('.slug', '.locale', $attribute));
                    if (PageTranslation::query()->where('locale', $locale)->where('slug', $value)
                        ->when($pageId, static fn ($query) => $query->where('page_id', '!=', $pageId))->exists()) {
                        $fail(__('page_builder.slug_taken'));
                    }
                },
            ],
        ];
    }
}
