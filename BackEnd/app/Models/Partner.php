<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'logo_media_id', 'website_url', 'status', 'is_featured', 'sort_order', 'published_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Partner extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    protected $table = 'hongvan_partners';

    public static function translationModelClass(): string
    {
        return PartnerTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'partner_id';
    }

    public static function translationNamespace(): string
    {
        return 'partners';
    }

    /** @return BelongsTo<Media, $this> */
    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
