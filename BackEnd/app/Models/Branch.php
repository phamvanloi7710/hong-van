<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'address', 'province', 'district', 'ward', 'postal_code', 'latitude', 'longitude', 'phone', 'email', 'is_head_office', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class Branch extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_branches';

    /** @return HasMany<BusinessHour, $this> */
    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class)->orderBy('day_of_week');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_head_office' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
