<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class RoleUser extends Pivot
{
    public $incrementing = false;

    public $timestamps = true;

    protected $table = 'hongvan_role_user';
}
