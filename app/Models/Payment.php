<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['reference', 'invoice_id', 'student_id', 'enrollment_id', 'service_id', 'academic_year_id', 'branch_id', 'created_by', 'payment_date', 'paid_until', 'next_payment_date', 'currency', 'exchange_rate', 'tuition_amount', 'administrative_fee', 'discount_type', 'discount_amount', 'total_amount', 'received_amount', 'payment_method', 'other_bank_name', 'status', 'notes'];

    protected function casts(): array
    {
        return ['payment_date' => 'date', 'paid_until' => 'date', 'next_payment_date' => 'date', 'exchange_rate' => 'decimal:4', 'tuition_amount' => 'decimal:2', 'administrative_fee' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total_amount' => 'decimal:2', 'received_amount' => 'decimal:2'];
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function items() { return $this->hasMany(PaymentItem::class); }
    public function paymentMonths() { return $this->hasMany(PaymentMonth::class); }
    public function allocations() { return $this->hasMany(PaymentAllocation::class); }
    public function refunds() { return $this->hasMany(Refund::class); }
    public function invoice() { return $this->hasOne(Invoice::class); }
    public function invoiceDocument() { return $this->belongsTo(Invoice::class, 'invoice_id'); }
    public function journalEntry() { return $this->morphOne(JournalEntry::class, 'source'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
