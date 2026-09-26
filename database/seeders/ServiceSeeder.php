<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name_kh' => 'គ្មាន', 'name_en' => 'None'],
            ['name_kh' => 'ថ្លៃដឹក', 'name_en' => 'Transportation'],
            ['name_kh' => 'ស្នាក់នៅ', 'name_en' => 'Accommodation'],
            ['name_kh' => 'ស្នាក់នៅញាំអាហារ', 'name_en' => 'Accommodation and meals'],
            ['name_kh' => 'ថ្លៃដឹកនិងស្នាក់នៅ', 'name_en' => 'Transportation and accommodation'],
            ['name_kh' => 'ថ្លៃដឹកនិងស្នាក់នៅញាំអាហារ', 'name_en' => 'Transportation and accommodation with meals'],
        ];

        foreach (Branch::query()->get() as $branch) {
            foreach ($services as $service) {
                Service::updateOrCreate(
                    ['branch_id' => $branch->id, 'name_kh' => $service['name_kh']],
                    [...$service, 'price' => 0, 'status' => 'active'],
                );
            }
        }
    }
}
