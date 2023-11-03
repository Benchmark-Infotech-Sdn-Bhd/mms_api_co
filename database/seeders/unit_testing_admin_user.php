<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class unit_testing_admin_user extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'unittestadmin',
            'email' => 'unittestadmin@gmail.com',
            'password' => '$2y$10$NV8KnNP9pHVcHF9k5V8yC.xLr4PKHsv/DNcL0G0dnrEoVIPRJbvdm',
            'user_type' => 'Admin',
            'company_id' => 1
        ]);
    }
}
