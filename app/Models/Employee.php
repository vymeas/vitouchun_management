<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = ['employee_id', 'name_kh', 'name_en', 'gender', 'dob', 'phone', 'email', 'address', 'emergency_contact', 'emergency_phone', 'photo', 'branch_id', 'department_id', 'position_id', 'user_id', 'joining_date', 'employment_type', 'basic_salary', 'status', 'remark'];
    protected function casts(): array { return ['dob' => 'date', 'joining_date' => 'date', 'basic_salary' => 'decimal:2']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function position() { return $this->belongsTo(Position::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
