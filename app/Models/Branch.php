<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_kh',
        'address',
        'phone',
        'email',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
        ];
    }

    // ==================== Relationships ====================

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ==================== Accessors ====================

    public function getDisplayNameAttribute(): string
    {
        return $this->name_kh ?: $this->name;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
