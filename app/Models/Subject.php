<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['code', 'name_kh', 'name_en', 'level', 'parent_subject_id', 'credit', 'type', 'subject_type', 'display_order', 'status', 'description', 'branch_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function grades()
    {
        return $this->belongsToMany(Grade::class, 'grade_subjects')
            ->withPivot(['display_order', 'status'])
            ->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_subject_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_subject_id')->orderBy('display_order')->orderBy('name_kh');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }
}
