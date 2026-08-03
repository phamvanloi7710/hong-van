<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'status', 'is_featured', 'sort_order', 'published_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Gallery extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    public const STATUSES = ['draft', 'published', 'archived'];

    protected $table = 'hongvan_galleries';

    public static function translationModelClass(): string
    {
        return GalleryTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'gallery_id';
    }

    public static function translationNamespace(): string
    {
        return 'galleries';
    }

    /** @return HasMany<GalleryItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order')->orderBy('id');
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
