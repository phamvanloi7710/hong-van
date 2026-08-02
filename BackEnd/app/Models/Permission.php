<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['key', 'module', 'action', 'name', 'description', 'is_system'])]
class Permission extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_permissions';

    /**
     * @return BelongsToMany<Role, $this, PermissionRole>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'hongvan_permission_role')
            ->using(PermissionRole::class)
            ->withPivot(['granted_by', 'created_at']);
    }

    /**
     * @return BelongsToMany<User, $this, UserPermissionOverride>
     */
    public function userOverrides(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hongvan_user_permission_overrides')
            ->using(UserPermissionOverride::class)
            ->withPivot(['is_allowed', 'assigned_by'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }
}
