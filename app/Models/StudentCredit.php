<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCredit extends Model
{
    protected $fillable = ['student_id', 'enrollment_id', 'amount', 'applied_amount', 'currency', 'status', 'source_payment_id', 'note'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'applied_amount' => 'decimal:2']; }
    public function student() { return $this->belongsTo(Student::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function sourcePayment() { return $this->belongsTo(Payment::class, 'source_payment_id'); }
    public function getAvailableAmountAttribute(): float { return max(0, (float) $this->amount - (float) $this->applied_amount); }
}