<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['code', 'name', 'type', 'branch_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branch() { return $this->belongsTo(Branch::class); }
    public function lines() { return $this->hasMany(JournalLine::class); }
}
