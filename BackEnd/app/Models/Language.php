<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['locale', 'name', 'native_name', 'is_active', 'is_default', 'sort_order'])]
final class Language extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_languages';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
