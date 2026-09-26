<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name'      => 'Main Branch',
                'name_kh'   => 'សាខាធំ',
                'address'   => 'Phnom Penh, Cambodia',
                'phone'     => '012 345 678',
                'email'     => 'main@vitouchun.edu.kh',
                'status'    => 'active',
                'opened_at' => '2024-01-01',
            ]
        );
    }
}
