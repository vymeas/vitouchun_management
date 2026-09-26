<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\StudyShift;
use Illuminate\Database\Seeder;

class StudyShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'វេនព្រឹក', 'start_time' => '07:30', 'end_time' => '11:00'],
            ['name' => 'វេនរសៀល', 'start_time' => '13:00', 'end_time' => '16:30'],
            ['name' => 'វេនល្ងាច 5:00-6:00', 'start_time' => '17:00', 'end_time' => '18:00'],
            ['name' => 'វេនល្ងាច 6:00-7:00', 'start_time' => '18:00', 'end_time' => '19:00'],
        ];

        foreach (Branch::query()->get() as $branch) {
            foreach ($shifts as $shift) {
                StudyShift::updateOrCreate(
                    ['branch_id' => $branch->id, 'name' => $shift['name']],
                    [...$shift, 'status' => 'active'],
                );
            }
        }
    }
}