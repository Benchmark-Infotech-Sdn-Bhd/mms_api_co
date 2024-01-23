<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class unit_testing_super_admin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'unittest',
            'email' => 'unittest@gmail.com',
            'password' => '$2y$10$NV8KnNP9pHVcHF9k5V8yC.xLr4PKHsv/DNcL0G0dnrEoVIPRJbvdm',
            'user_type' => 'Super Admin',
            'company_id' => 0
        ]);
    }
}
