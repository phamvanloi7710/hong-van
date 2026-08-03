<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['crop_id', 'crop_stage_id', 'code', 'status', 'hero_media_id', 'is_featured', 'sort_order', 'published_at', 'unpublished_at', 'created_by', 'updated_by', 'deleted_by'])]
final class CropSolution extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_crop_solutions';

    public static function translationModelClass(): string
    {
        return CropSolutionTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'crop_solution_id';
    }

    public static function translationNamespace(): string
    {
        return 'crop_solutions';
    }

    /** @return BelongsTo<Crop, $this> */
    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    /** @return BelongsTo<CropStage, $this> */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(CropStage::class, 'crop_stage_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'hongvan_crop_solution_products')
            ->withPivot(['sort_order', 'recommendation_note', 'created_at']);
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
