<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCard extends Model
{
    protected $fillable = ['branch_id', 'student_id', 'enrollment_id', 'academic_year_id', 'card_number', 'card_type', 'issue_date', 'expiry_date', 'status', 'qr_token', 'created_by', 'updated_by'];
    protected function casts(): array { return ['issue_date' => 'date', 'expiry_date' => 'date']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
