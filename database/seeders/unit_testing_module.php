<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class unit_testing_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Module::create(['id' => 1, 'module_name' => 'CRM', 'module_url' => '', 'parent_id' => 0, 'order_id' => 1, 'status' => 1]);
        Module::create(['id' => 2, 'module_name' => 'Services', 'module_url' => '', 'parent_id' => 0, 'order_id' => 2, 'status' => 1]);
        Module::create(['id' => 3, 'module_name' => 'Reports', 'module_url' => '', 'parent_id' => 0, 'order_id' => 3, 'status' => 1]);
        Module::create(['id' => 4, 'module_name' => 'Worker Management', 'module_url' => '', 'parent_id' => 0, 'order_id' => 4, 'status' => 1]);
        Module::create(['id' => 5, 'module_name' => 'Dispatch Management', 'module_url' => '', 'parent_id' => 0, 'order_id' => 5, 'status' => 1]);
        Module::create(['id' => 6, 'module_name' => 'Direct Recruitment', 'module_url' => '', 'parent_id' => 2, 'order_id' => 1, 'status' => 1]);
        Module::create(['id' => 7, 'module_name' => 'e-Contract', 'module_url' => '', 'parent_id' => 2, 'order_id' => 2, 'status' => 1]);
        Module::create(['id' => 8, 'module_name' => 'Total Management', 'module_url' => '', 'parent_id' => 2, 'order_id' => 3, 'status' => 1]);
    }
}
