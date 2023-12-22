<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class unit_testing_modules extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Module::create(['id' => 1, 'module_name' => 'Dashboard', 'module_url' => '', 'parent_id' => 0, 'order_id' => 1, 'status' => 1]);
    }
}
