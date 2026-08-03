<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['gallery_id', 'media_id', 'status', 'is_featured', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class GalleryItem extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    protected $table = 'hongvan_gallery_items';

    public static function translationModelClass(): string
    {
        return GalleryItemTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'gallery_item_id';
    }

    public static function translationNamespace(): string
    {
        return 'gallery_items';
    }

    /** @return BelongsTo<Gallery, $this> */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }
}
