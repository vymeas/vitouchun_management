<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name_kh', 'name_en', 'price', 'status', 'branch_id'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function branch() { return $this->belongsTo(Branch::class); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeForBranch($query, ?int $branchId) { return $branchId ? $query->where('branch_id', $branchId) : $query; }
}
