<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public const STATUSES = ['present', 'absent', 'late', 'excused'];

    protected $fillable = ['branch_id', 'academic_year_id', 'term_id', 'class_id', 'student_id', 'enrollment_id', 'attendance_date', 'status', 'check_in_time', 'check_out_time', 'reason', 'note', 'marked_by', 'updated_by'];

    protected function casts(): array { return ['attendance_date' => 'date']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function term() { return $this->belongsTo(Term::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function student() { return $this->belongsTo(Student::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function marker() { return $this->belongsTo(User::class, 'marked_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
