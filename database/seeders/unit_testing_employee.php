<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class unit_testing_employee extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::create([
            'employee_name' => 'test',
            'gender' => 'gender',
            'date_of_birth' => '1998-11-02',
            'ic_number' => 222223434,
            'passport_number' => 'ADI',
            'email' => 'testemployee@gmail.com',
            'contact_number' => 8754686787,
            'address' => 'address',
            'postcode' => 7584,
            'position' => 'position',
            'branch_id' => 1,
            'role_id' => 1,
            'salary' => 20000,
            'status' => 1,
            'city' => 'city',
            'state' => 'state',
        ]);
    }
}
