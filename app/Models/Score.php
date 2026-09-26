<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'academic_year_id', 'class_id', 'term_id', 'branch_id', 'created_by', 'updated_by', 'score', 'maximum_score', 'grade', 'result'];
    protected function casts(): array { return ['score' => 'decimal:2', 'maximum_score' => 'decimal:2']; }
    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function term() { return $this->belongsTo(Term::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
