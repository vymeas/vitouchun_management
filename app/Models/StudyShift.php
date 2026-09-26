<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyShift extends Model
{
    protected $fillable = ['branch_id', 'name', 'start_time', 'end_time', 'status'];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function classes() { return $this->hasMany(SchoolClass::class, 'shift_id'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}