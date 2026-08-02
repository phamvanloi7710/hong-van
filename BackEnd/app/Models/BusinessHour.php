<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'scope_key', 'day_of_week', 'opens_at', 'closes_at', 'is_closed', 'note', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class BusinessHour extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_business_hours';

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['day_of_week' => 'integer', 'is_closed' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
