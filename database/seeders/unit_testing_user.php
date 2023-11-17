<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;

class unit_testing_user extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'company_name' => 'Test Company', 
            'register_number' => 'APS646-46876', 
            'country' => 'India', 
            'state' => 'TamilNadu', 
            'pic_name' => 'TestPIC', 
            'role' => 'Admin'
        ]);

        User::create([
            'name' => 'unittest',
            'email' => 'unittest@gmail.com',
            'password' => '$2y$10$NV8KnNP9pHVcHF9k5V8yC.xLr4PKHsv/DNcL0G0dnrEoVIPRJbvdm',
            'company_id' => 1,
            'user_type' => 'Admin',
            'reference_id' => 0,
            'pic_flag' => 0
        ]);
    }
}
