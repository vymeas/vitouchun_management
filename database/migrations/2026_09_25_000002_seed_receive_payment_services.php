<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $services = [
            ['name_kh' => 'គ្មាន', 'name_en' => 'None'],
            ['name_kh' => 'ថ្លៃដឹក', 'name_en' => 'Transportation'],
            ['name_kh' => 'ស្នាក់នៅ', 'name_en' => 'Accommodation'],
            ['name_kh' => 'ស្នាក់នៅញាំអាហារ', 'name_en' => 'Accommodation and meals'],
            ['name_kh' => 'ថ្លៃដឹកនិងស្នាក់នៅ', 'name_en' => 'Transportation and accommodation'],
            ['name_kh' => 'ថ្លៃដឹកនិងស្នាក់នៅញាំអាហារ', 'name_en' => 'Transportation and accommodation with meals'],
        ];

        foreach (DB::table('branches')->pluck('id') as $branchId) {
            foreach ($services as $service) {
                $existing = DB::table('services')
                    ->where('branch_id', $branchId)
                    ->where('name_kh', $service['name_kh'])
                    ->first();

                if ($existing) {
                    DB::table('services')->where('id', $existing->id)->update([
                        'name_en' => $service['name_en'],
                        'updated_at' => now(),
                    ]);
                    continue;
                }

                DB::table('services')->insert([
                    'branch_id' => $branchId,
                    ...$service,
                    'price' => 0,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('services')->whereIn('name_kh', [
            'គ្មាន',
            'ថ្លៃដឹក',
            'ស្នាក់នៅ',
            'ស្នាក់នៅញាំអាហារ',
            'ថ្លៃដឹកនិងស្នាក់នៅ',
            'ថ្លៃដឹកនិងស្នាក់នៅញាំអាហារ',
        ])->delete();
    }
};