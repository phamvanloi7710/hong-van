<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['status', 'goods_description', 'required_area', 'area_unit', 'required_volume', 'volume_unit', 'duration_description', 'start_date', 'storage_requirements', 'preferred_location', 'warehouse_id', 'contact_name', 'contact_phone', 'contact_email', 'ip_hash', 'user_agent_hash'])]
final class WarehouseRequest extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_warehouse_requests';

    /** @return BelongsTo<Warehouse, $this> */
    public function preferredWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /** @return HasMany<WarehouseRequestStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(WarehouseRequestStatusHistory::class);
    }

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['required_area' => 'decimal:3', 'required_volume' => 'decimal:3', 'start_date' => 'immutable_date'];
    }
}
