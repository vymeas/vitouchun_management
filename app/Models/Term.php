<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = ['name', 'academic_year_id', 'grade_id', 'branch_id', 'start_date', 'end_date', 'maximum_score', 'status'];
    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date', 'maximum_score' => 'decimal:2']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function grade() { return $this->belongsTo(Grade::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function scores() { return $this->hasMany(Score::class); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
