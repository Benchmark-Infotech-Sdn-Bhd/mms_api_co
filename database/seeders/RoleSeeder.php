<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['id' => 1, 'role_name' => 'Admin', 'system_role' => 0, 'status' => 1, 'parent_id' => 0]);
    }
}