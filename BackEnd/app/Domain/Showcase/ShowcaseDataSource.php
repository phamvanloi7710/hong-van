<?php

namespace App\Domain\Showcase;

use App\Models\Certification;
use App\Models\Gallery;
use App\Models\Partner;
use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class ShowcaseDataSource
{
    /** @return array{galleries:list<array<string,mixed>>,partners:list<array<string,mixed>>,certifications:list<array<string,mixed>>,projects:list<array<string,mixed>>} */
    public function published(string $locale): array
    {
        $galleries = Gallery::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))->with(['translations', 'items' => fn ($query) => $query->where('status', 'published')->with(['translations', 'media'])])->orderBy('sort_order')->orderBy('id')->get();
        $partners = Partner::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))->with(['translations', 'logo'])->orderBy('sort_order')->orderBy('id')->get();
        $certifications = Certification::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))->with(['translations', 'image', 'document'])->orderBy('sort_order')->orderBy('id')->get();
        $projects = Project::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))->with(['translations', 'mediaItems.translations', 'mediaItems.media'])->orderBy('sort_order')->orderBy('id')->get();

        return [
            'galleries' => $galleries->map(fn (Gallery $item): array => $this->gallery($item, $locale))->all(),
            'partners' => $partners->map(fn (Partner $item): array => $this->partner($item, $locale))->all(),
            'certifications' => $certifications->map(fn (Certification $item): array => $this->certification($item, $locale))->all(),
            'projects' => $projects->map(fn (Project $item): array => $this->project($item, $locale))->all(),
        ];
    }

    /** @return array<string,mixed> */
    private function gallery(Gallery $item, string $locale): array
    {
        $translation = $this->translation($item->translations, $locale);

        return ['public_id' => $item->public_id, 'code' => $item->code, 'name' => $translation?->getAttribute('name'), 'slug' => $translation?->getAttribute('slug'), 'description' => $translation?->getAttribute('description'), 'is_featured' => $item->is_featured, 'items' => $item->items->map(function ($media) use ($locale): array {
            $translation = $this->translation($media->translations, $locale);

            return ['public_id' => $media->public_id, 'media_public_id' => $media->media?->public_id, 'mime_type' => $media->media?->mime_type, 'title' => $translation?->getAttribute('title'), 'caption' => $translation?->getAttribute('caption'), 'alt_text' => $translation?->getAttribute('alt_text')];
        })->all()];
    }

    /** @return array<string,mixed> */
    private function partner(Partner $item, string $locale): array
    {
        $translation = $this->translation($item->translations, $locale);

        return ['public_id' => $item->public_id, 'code' => $item->code, 'name' => $translation?->getAttribute('name'), 'description' => $translation?->getAttribute('description'), 'logo_alt' => $translation?->getAttribute('logo_alt'), 'logo_media_public_id' => $item->logo?->public_id, 'website_url' => $item->website_url, 'is_featured' => $item->is_featured];
    }

    /** @return array<string,mixed> */
    private function certification(Certification $item, string $locale): array
    {
        $translation = $this->translation($item->translations, $locale);

        return ['public_id' => $item->public_id, 'code' => $item->code, 'name' => $translation?->getAttribute('name'), 'slug' => $translation?->getAttribute('slug'), 'issuer' => $translation?->getAttribute('issuer'), 'description' => $translation?->getAttribute('description'), 'image_alt' => $translation?->getAttribute('image_alt'), 'image_media_public_id' => $item->image?->public_id, 'document_label' => $item->document_visibility === 'public' ? $translation?->getAttribute('document_label') : null, 'document_media_public_id' => $item->document_visibility === 'public' ? $item->document?->public_id : null, 'issued_on' => $this->date($item, 'issued_on'), 'expires_on' => $this->date($item, 'expires_on'), 'is_featured' => $item->is_featured];
    }

    /** @return array<string,mixed> */
    private function project(Project $item, string $locale): array
    {
        $translation = $this->translation($item->translations, $locale);

        return ['public_id' => $item->public_id, 'code' => $item->code, 'title' => $translation?->getAttribute('title'), 'slug' => $translation?->getAttribute('slug'), 'summary' => $translation?->getAttribute('summary'), 'content' => $translation?->getAttribute('content'), 'location' => $translation?->getAttribute('location'), 'started_on' => $this->date($item, 'started_on'), 'completed_on' => $this->date($item, 'completed_on'), 'is_featured' => $item->is_featured, 'media_items' => $item->mediaItems->map(function ($media) use ($locale): array {
            $translation = $this->translation($media->translations, $locale);

            return ['public_id' => $media->public_id, 'role' => $media->role, 'media_public_id' => $media->media?->public_id, 'mime_type' => $media->media?->mime_type, 'alt_text' => $translation?->getAttribute('alt_text'), 'caption' => $translation?->getAttribute('caption')];
        })->all()];
    }

    /** @param Collection<int, Model> $translations */
    private function translation(Collection $translations, string $locale): ?Model
    {
        return $translations->firstWhere('locale', $locale) ?? $translations->firstWhere('locale', 'vi') ?? $translations->first();
    }

    private function date(Model $model, string $field): ?string
    {
        $value = $model->getAttribute($field);

        return $value === null ? null : CarbonImmutable::parse((string) $value)->toDateString();
    }
}
