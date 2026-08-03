<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lead_id', 'transport_request_id', 'warehouse_request_id', 'created_at'])]
final class LeadRequestLink extends Model
{
    public $timestamps = false;

    protected $table = 'hongvan_lead_request_links';

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<TransportRequest, $this> */
    public function transportRequest(): BelongsTo
    {
        return $this->belongsTo(TransportRequest::class);
    }

    /** @return BelongsTo<WarehouseRequest, $this> */
    public function warehouseRequest(): BelongsTo
    {
        return $this->belongsTo(WarehouseRequest::class);
    }

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }
}
