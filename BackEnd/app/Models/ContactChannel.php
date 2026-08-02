<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'label', 'value', 'href', 'availability_note', 'is_primary', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class ContactChannel extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_contact_channels';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
