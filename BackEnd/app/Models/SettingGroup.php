<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'label', 'description', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class SettingGroup extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_setting_groups';

    /** @return HasMany<Setting, $this> */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class)->orderBy('sort_order');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
