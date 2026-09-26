<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'payment_id', 'branch_id', 'total_amount', 'currency', 'status'];
    protected function casts(): array { return ['total_amount' => 'decimal:2']; }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function payments() { return $this->hasMany(Payment::class, 'invoice_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
