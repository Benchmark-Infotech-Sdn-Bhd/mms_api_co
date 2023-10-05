<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Module::create(['id' => 1, 'module_name' => 'Dashboard', 'module_url' => '', 'parent_id' => 0, 'order_id' => 1, 'status' => 1]);
        Module::create(['id' => 2, 'module_name' => 'Maintain Masters', 'module_url' => '', 'parent_id' => 0, 'order_id' => 2, 'status' => 0]);
        Module::create(['id' => 3, 'module_name' => 'Branches', 'module_url' => '', 'parent_id' => 0, 'order_id' => 3, 'status' => 1]);
        Module::create(['id' => 4, 'module_name' => 'CRM', 'module_url' => '', 'parent_id' => 0, 'order_id' => 4, 'status' => 1]);
        Module::create(['id' => 5, 'module_name' => 'Direct Recruitment', 'module_url' => '', 'parent_id' => 0, 'order_id' => 5, 'status' => 1]);
        Module::create(['id' => 6, 'module_name' => 'e-Contract', 'module_url' => '', 'parent_id' => 0, 'order_id' => 6, 'status' => 1]);
        Module::create(['id' => 7, 'module_name' => 'Total Management', 'module_url' => '', 'parent_id' => 0, 'order_id' => 7, 'status' => 1]);
        Module::create(['id' => 8, 'module_name' => 'Employee', 'module_url' => '', 'parent_id' => 0, 'order_id' => 8, 'status' => 1]);
        Module::create(['id' => 9, 'module_name' => 'Access Management', 'module_url' => '', 'parent_id' => 0, 'order_id' => 9, 'status' => 1]);
        Module::create(['id' => 10, 'module_name' => 'Workers', 'module_url' => '', 'parent_id' => 0, 'order_id' => 10, 'status' => 1]);
        Module::create(['id' => 11, 'module_name' => 'Dispatch Management', 'module_url' => '', 'parent_id' => 0, 'order_id' => 11, 'status' => 1]);
        Module::create(['id' => 12, 'module_name' => 'Invoice', 'module_url' => '', 'parent_id' => 0, 'order_id' => 12, 'status' => 1]);
        Module::create(['id' => 13, 'module_name' => 'Reports', 'module_url' => '', 'parent_id' => 2, 'order_id' => 13, 'status' => 1]);
    }
}