<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['reference', 'branch_id', 'created_by', 'approved_by', 'expense_date', 'category', 'description', 'amount', 'currency', 'payment_method', 'status', 'notes'];
    protected function casts(): array { return ['expense_date' => 'date', 'amount' => 'decimal:2']; }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function journalEntry() { return $this->morphOne(JournalEntry::class, 'source'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
