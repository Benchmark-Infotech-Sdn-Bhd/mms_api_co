<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemType;

class SystemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemType::create(['id' => 1, 'system_name' => 'FWCMS', 'status' => 1]);
        SystemType::create(['id' => 2, 'system_name' => 'FOMEMA', 'status' => 1]);
        SystemType::create(['id' => 3, 'system_name' => 'Email Login Credentials', 'status' => 1]);
        SystemType::create(['id' => 4, 'system_name' => 'EPLKS', 'status' => 1]);
        SystemType::create(['id' => 5, 'system_name' => 'Myfuture Jobs', 'status' => 1]);
        SystemType::create(['id' => 6, 'system_name' => 'ESD', 'status' => 1]);
        SystemType::create(['id' => 7, 'system_name' => 'Pin Keselamatan', 'status' => 1]);
    }
}