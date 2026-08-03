<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'user_id', 'token_hash', 'expires_at', 'refreshed_at'])]
final class PageLock extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_locks';

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['expires_at' => 'immutable_datetime', 'refreshed_at' => 'immutable_datetime'];
    }
}
