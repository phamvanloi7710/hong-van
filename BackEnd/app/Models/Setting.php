<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['setting_group_id', 'key', 'label', 'description', 'value', 'value_type', 'is_public', 'is_locked', 'sort_order', 'created_by', 'updated_by'])]
final class Setting extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_settings';

    /** @return BelongsTo<SettingGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SettingGroup::class, 'setting_group_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'is_locked' => 'boolean', 'sort_order' => 'integer'];
    }
}
