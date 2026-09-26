<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['code', 'name_kh', 'name_en', 'branch_id', 'manager_id', 'is_active', 'description'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function employees() { return $this->hasMany(Employee::class); }
    public function manager() { return $this->belongsTo(Employee::class, 'manager_id'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
