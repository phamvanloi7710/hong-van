<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['locale', 'normalized_term', 'term_hash', 'types', 'results_count', 'visitor_hash', 'created_at'])]
final class SearchLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'hongvan_search_logs';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['types' => 'array', 'results_count' => 'integer', 'created_at' => 'immutable_datetime'];
    }
}
