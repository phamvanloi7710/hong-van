<?php

namespace App\Models;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'description', 'is_system'])]
class Role extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_roles';

    /**
     * @return BelongsToMany<User, $this, RoleUser>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hongvan_role_user')
            ->using(RoleUser::class)
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Permission, $this, PermissionRole>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'hongvan_permission_role')
            ->using(PermissionRole::class)
            ->withPivot('granted_by')
            ->withTimestamps();
    }

    public function isSuperAdminRole(): bool
    {
        return $this->slug === PermissionRegistry::SUPER_ADMIN_ROLE;
    }

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }
}
