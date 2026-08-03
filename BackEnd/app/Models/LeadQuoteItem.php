<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['lead_id', 'product_id', 'product_name_snapshot', 'quantity', 'unit', 'notes', 'created_at'])]
final class LeadQuoteItem extends Model
{
    public $timestamps = false;

    protected $table = 'hongvan_lead_quote_items';

    protected static function booted(): void
    {
        self::updating(fn () => throw new LogicException('Original quote items are immutable.'));
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'notes' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }
}
