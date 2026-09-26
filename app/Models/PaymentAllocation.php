<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    protected $fillable = ['payment_id', 'payment_month_id', 'amount', 'currency'];
    protected function casts(): array { return ['amount' => 'decimal:2']; }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function paymentMonth() { return $this->belongsTo(PaymentMonth::class); }
}