<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'full_name',
        'full_name_kh',
        'email',
        'phone',
        'password',
        'branch_id',
        'role',
        'status',
        'is_deleted',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_deleted'        => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withPivot('granted')
                    ->withTimestamps();
    }

    public function teacherClassAssignments() { return $this->hasMany(TeacherClassAssignment::class, 'teacher_id'); }
    public function teacherSubjectAssignments() { return $this->hasMany(TeacherSubjectAssignment::class, 'teacher_id'); }
    public function teacherSchedules() { return $this->hasMany(TeacherSchedule::class, 'teacher_id'); }
    public function teacherProfile() { return $this->hasOne(TeacherProfile::class); }

    // ==================== Role Helpers ====================

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    // ==================== Permission Helpers ====================

    /**
     * Check if user has a given permission.
     * Super Admin always has all permissions.
     * Otherwise checks role-based and user-specific permissions.
     */
    public function can($ability, $arguments = []): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check cached user permissions
        $permissions = $this->getCachedPermissions();

        return isset($permissions[$ability]) && $permissions[$ability] === true;
    }

    /**
     * Get all permissions for this user (role + user-specific), cached in session.
     */
    public function getCachedPermissions(): array
    {
        $cacheKey = 'user_permissions_' . $this->id;

        return cache()->remember($cacheKey, now()->addHours(1), function () {
            $permissions = [];

            // Load role-level permissions
            $rolePerms = \DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role', $this->role)
                ->pluck('permissions.name')
                ->toArray();

            foreach ($rolePerms as $perm) {
                $permissions[$perm] = true;
            }

            // Load user-specific overrides
            $userPerms = $this->permissions()->get();
            foreach ($userPerms as $perm) {
                $permissions[$perm->name] = (bool) $perm->pivot->granted;
            }

            return $permissions;
        });
    }

    /**
     * Clear cached permissions for this user
     */
    public function clearPermissionCache(): void
    {
        cache()->forget('user_permissions_' . $this->id);
    }

    // ==================== Branch Helpers ====================

    /**
     * Get the branch ID this user can access.
     * Super admin can access any branch (returns null = all).
     */
    public function getAccessibleBranchId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null; // null = all branches
        }
        return $this->branch_id;
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_deleted', false);
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }

    // ==================== Accessors ====================

    public function getNameAttribute(): string
    {
        return $this->full_name ?: ($this->full_name_kh ?? '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name_kh ?: $this->full_name;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'accountant'  => 'គណនេយ្យ',
            'registrar'   => 'ការិយាល័យ',
            'teacher'     => 'គ្រូ',
            'staff'       => 'បុគ្គលិក',
            default       => $this->role,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'សកម្ម',
            'inactive'  => 'អសកម្ម',
            'suspended' => 'ព្យួរ',
            default     => $this->status,
        };
    }
}
