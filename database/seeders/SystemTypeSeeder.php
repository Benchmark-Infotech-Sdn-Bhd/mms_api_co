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
    }
}