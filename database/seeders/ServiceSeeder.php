<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Services;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Services::create(['id' => 1, 'service_name' => 'Direct Recruitment', 'status' => 1, 'module_id' => 5]);
        Services::create(['id' => 2, 'service_name' => 'e-Contract', 'status' => 1, 'module_id' => 6]);
        Services::create(['id' => 3, 'service_name' => 'Total Management', 'status' => 1, 'module_id' => 7]);
    }
}
