<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source_path', 'locale', 'target_path', 'status_code', 'is_active', 'hit_count', 'last_hit_at', 'note', 'created_by', 'updated_by'])]
final class RedirectRule extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_redirects';

    protected function casts(): array
    {
        return ['status_code' => 'integer', 'is_active' => 'boolean', 'hit_count' => 'integer', 'last_hit_at' => 'immutable_datetime'];
    }
}
