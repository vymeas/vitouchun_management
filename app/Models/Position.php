<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['code', 'name_kh', 'name_en', 'department_id', 'branch_id', 'is_active', 'description'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function employees() { return $this->hasMany(Employee::class); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
