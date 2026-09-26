<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            ServiceSeeder::class,
            StudyShiftSeeder::class,
            // SettingSeeder::class,
        ]);
    }
}
