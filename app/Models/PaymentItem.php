<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $fillable = ['payment_id', 'description', 'amount'];
    protected function casts(): array { return ['amount' => 'decimal:2']; }
    public function payment() { return $this->belongsTo(Payment::class); }
}
