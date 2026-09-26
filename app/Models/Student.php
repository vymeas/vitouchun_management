<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'code',
        'student_code',
        'name_kh',
        'khmer_name',
        'name_en',
        'english_name',
        'gender',
        'dob',
        'date_of_birth',
        'phone',
        'address',
        'current_address',
        'place_of_birth',
        'parent_name',
        'father_name',
        'mother_name',
        'parent_phone',
        'father_phone',
        'mother_phone',
        'father_occupation',
        'mother_occupation',
        'parent_occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'student_type',
        'health_condition',
        'characteristics',
        'photo',
        'status',
        'created_by',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'date_of_birth' => 'date',
        ];
    }

    public function getKhmerNameAttribute(): ?string
    {
        return $this->attributes['khmer_name'] ?? $this->attributes['name_kh'] ?? null;
    }

    public function setKhmerNameAttribute($value): void
    {
        $this->attributes['khmer_name'] = $value;
        $this->attributes['name_kh'] = $value;
    }

    public function getEnglishNameAttribute(): ?string
    {
        return $this->attributes['english_name'] ?? $this->attributes['name_en'] ?? null;
    }

    public function setEnglishNameAttribute($value): void
    {
        $this->attributes['english_name'] = $value;
        $this->attributes['name_en'] = $value;
    }

    public function getStudentCodeAttribute(): ?string
    {
        return $this->attributes['student_code'] ?? $this->attributes['code'] ?? null;
    }

    public function setStudentCodeAttribute($value): void
    {
        $this->attributes['student_code'] = $value;
        $this->attributes['code'] = $value;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function cards()
    {
        return $this->hasMany(StudentCard::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Latest posted payment per student, resolved via a single query (no N+1) for payment history listings.
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->ofMany(
            ['payment_date' => 'max', 'id' => 'max'],
            fn ($query) => $query->where('status', 'posted')
        );
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }

        return $query;
    }
}
