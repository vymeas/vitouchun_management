<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $branches = DB::table('branches')->pluck('id');

        foreach ($branches as $branchId) {
            DB::table('services')
                ->where('branch_id', $branchId)
                ->where('name_kh', 'ថ្លៃដឹក + ស្នាក់នៅញាំអាហារ')
                ->update([
                    'name_kh' => 'ថ្លៃដឹកនិងស្នាក់នៅញាំអាហារ',
                    'name_en' => 'Transportation and accommodation with meals',
                    'updated_at' => now(),
                ]);
            DB::table('services')->updateOrInsert(
                ['branch_id' => $branchId, 'name_kh' => 'ថ្លៃដឹកនិងស្នាក់នៅ'],
                [
                    'name_en' => 'Transportation and accommodation',
                    'price' => 0,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('services')->where('name_kh', 'ថ្លៃដឹកនិងស្នាក់នៅ')->delete();
    }
};