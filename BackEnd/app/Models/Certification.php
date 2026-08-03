<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'image_media_id', 'document_media_id', 'document_visibility', 'issued_on', 'expires_on', 'status', 'is_featured', 'sort_order', 'published_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Certification extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    protected $table = 'hongvan_certifications';

    public static function translationModelClass(): string
    {
        return CertificationTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'certification_id';
    }

    public static function translationNamespace(): string
    {
        return 'certifications';
    }

    /** @return BelongsTo<Media, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'document_media_id');
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'issued_on' => 'immutable_date', 'expires_on' => 'immutable_date', 'published_at' => 'immutable_datetime'];
    }
}
