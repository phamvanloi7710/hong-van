<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** @property array<string, mixed> $document_json */
#[Fillable(['page_template_id', 'version_number', 'status', 'schema_version', 'document_json', 'checksum', 'parent_version_id', 'created_by', 'published_at'])]
final class PageTemplateVersion extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_template_versions';

    protected static function booted(): void
    {
        self::updating(function (self $version): void {
            if ($version->getOriginal('status') === 'published') {
                throw new LogicException('Published Page Builder template versions are immutable.');
            }
        });
        self::deleting(function (self $version): void {
            if ($version->status === 'published') {
                throw new LogicException('Published Page Builder template versions cannot be deleted.');
            }
        });
    }

    /** @return BelongsTo<PageTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(PageTemplate::class, 'page_template_id');
    }

    protected function casts(): array
    {
        return ['document_json' => 'array', 'schema_version' => 'integer', 'published_at' => 'immutable_datetime'];
    }
}
