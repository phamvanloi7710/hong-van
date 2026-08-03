<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['code', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at', 'created_by', 'updated_by'])]
final class TransportServiceArea extends TranslatableModel
{
    use HasPublicId;

    protected $table = 'hongvan_transport_service_areas';

    public static function translationModelClass(): string
    {
        return TransportServiceAreaTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'transport_service_area_id';
    }

    public static function translationNamespace(): string
    {
        return 'transport_service_areas';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'published_at' => 'immutable_datetime', 'unpublished_at' => 'immutable_datetime'];
    }
}
