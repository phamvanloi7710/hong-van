<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['page_id', 'version_number', 'status', 'schema_version', 'document_json', 'checksum', 'parent_version_id', 'created_by', 'published_by', 'published_at'])]
final class PageVersion extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_versions';

    protected static function booted(): void
    {
        self::updating(function (self $version): void {
            if ($version->getOriginal('status') === 'published') {
                throw new LogicException('Published Page Builder versions are immutable.');
            }
        });
        self::deleting(function (self $version): void {
            if ($version->status === 'published') {
                throw new LogicException('Published Page Builder versions cannot be deleted.');
            }
        });
    }

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<PageVersion, $this> */
    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_version_id');
    }

    protected function casts(): array
    {
        return [
            'document_json' => 'array',
            'schema_version' => 'integer',
            'published_at' => 'immutable_datetime',
        ];
    }
}
