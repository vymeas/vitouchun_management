<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = ['reference', 'branch_id', 'created_by', 'entry_date', 'description', 'source_type', 'source_id', 'status'];
    protected function casts(): array { return ['entry_date' => 'date']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function lines() { return $this->hasMany(JournalLine::class); }
    public function source() { return $this->morphTo(); }
}
