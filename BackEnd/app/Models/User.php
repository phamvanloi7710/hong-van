<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasPublicId;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPublicId, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hongvan_users';

    /**
     * Get the database notifications for the user.
     *
     * @return MorphMany<DatabaseNotification, $this>
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }

    /**
     * @return BelongsToMany<Role, $this, RoleUser>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'hongvan_role_user')
            ->using(RoleUser::class)
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * @return HasMany<UserPreference, $this>
     */
    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * @return BelongsToMany<Permission, $this, UserPermissionOverride>
     */
    public function permissionOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'hongvan_user_permission_overrides')
            ->using(UserPermissionOverride::class)
            ->withPivot(['is_allowed', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'locked_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
