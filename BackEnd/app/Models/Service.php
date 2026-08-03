<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['service_category_id', 'code', 'service_type', 'status', 'cta_type', 'is_featured', 'sort_order', 'published_at', 'unpublished_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Service extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_services';

    public static function translationModelClass(): string
    {
        return ServiceTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'service_id';
    }

    public static function translationNamespace(): string
    {
        return 'services';
    }

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /** @return BelongsToMany<Media, $this> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'hongvan_service_media')
            ->withPivot(['role', 'sort_order', 'created_at']);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'immutable_datetime',
            'unpublished_at' => 'immutable_datetime',
        ];
    }
}
