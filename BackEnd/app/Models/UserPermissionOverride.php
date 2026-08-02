<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class UserPermissionOverride extends Pivot
{
    public $incrementing = false;

    public $timestamps = true;

    protected $table = 'hongvan_user_permission_overrides';

    protected function casts(): array
    {
        return [
            'is_allowed' => 'boolean',
        ];
    }
}
