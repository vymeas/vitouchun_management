<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $fillable = [
        'payment_id',
        'description',
        'amount',
        'unit_price',
        'duration_months',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'duration_months' => 'integer',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}