<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class unit_testing_company extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'company_name' => 'unittestcompany',
            'register_number' => 'APS-646-4687',
            'country' => 'India',
            'state' => 'TamilNadu',
            'pic_name' => '',
            'role' => 'Admin'
        ]);
    }
}
