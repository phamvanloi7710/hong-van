<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class PermissionRole extends Pivot
{
    public $incrementing = false;

    public $timestamps = true;

    protected $table = 'hongvan_permission_role';
}
