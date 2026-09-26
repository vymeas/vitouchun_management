<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $mainBranch = Branch::where('code', 'MAIN')->first();

        // Super Admin
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'full_name'    => 'Super Administrator',
                'full_name_kh' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
                'email'        => 'superadmin@vitouchun.edu.kh',
                'phone'        => '011 222 333',
                'password'     => Hash::make('password'),
                'branch_id'    => null, // Super admin can access all branches
                'role'         => 'super_admin',
                'status'       => 'active',
                'is_deleted'   => false,
            ]
        );

        // Default application administrator for initial access.
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'full_name'    => 'System Admin',
                'full_name_kh' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
                'email'        => 'admin@school.local',
                'phone'        => '010000000',
                'password'     => Hash::make('admin123'),
                'branch_id'    => null,
                'role'         => 'super_admin',
                'status'       => 'active',
                'is_deleted'   => false,
            ]
        );
    }
}
