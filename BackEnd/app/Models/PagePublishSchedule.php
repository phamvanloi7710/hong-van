<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'page_version_id', 'action', 'status', 'scheduled_at', 'processed_at', 'failure_message', 'created_by'])]
final class PagePublishSchedule extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_publish_schedules';

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

    protected function casts(): array
    {
        return ['scheduled_at' => 'immutable_datetime', 'processed_at' => 'immutable_datetime'];
    }
}
