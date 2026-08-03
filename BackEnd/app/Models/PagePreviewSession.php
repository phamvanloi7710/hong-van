<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'page_version_id', 'user_id', 'token_hash', 'locale', 'expires_at', 'last_viewed_at'])]
final class PagePreviewSession extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_preview_sessions';

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<PageVersion, $this> */
    public function pageVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['expires_at' => 'immutable_datetime', 'last_viewed_at' => 'immutable_datetime'];
    }
}
