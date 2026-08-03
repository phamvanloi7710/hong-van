<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['status', 'pickup_location', 'delivery_location', 'cargo_description', 'cargo_weight', 'weight_unit', 'vehicle_type_id', 'requested_date', 'contact_name', 'contact_phone', 'contact_email', 'ip_hash', 'user_agent_hash'])]
final class TransportRequest extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_transport_requests';

    /** @return BelongsTo<VehicleType, $this> */
    public function preferredVehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /** @return HasMany<TransportRequestStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(TransportRequestStatusHistory::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['cargo_weight' => 'decimal:3', 'requested_date' => 'immutable_date'];
    }
}
