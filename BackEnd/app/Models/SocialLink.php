<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['platform', 'label', 'url', 'icon', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class SocialLink extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_social_links';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
