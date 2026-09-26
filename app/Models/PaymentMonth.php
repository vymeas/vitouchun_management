<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMonth extends Model
{
    protected $fillable = ['payment_id', 'enrollment_id', 'academic_year_id', 'month_key', 'type', 'month_start'];

    protected function casts(): array
    {
        return ['month_start' => 'date'];
    }

    public function payment() { return $this->belongsTo(Payment::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
