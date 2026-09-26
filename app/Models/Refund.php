<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = ['payment_id', 'refund_no', 'amount', 'currency', 'reason', 'refund_method', 'refund_date', 'status', 'approved_by', 'processed_by', 'note'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'refund_date' => 'date']; }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }
}