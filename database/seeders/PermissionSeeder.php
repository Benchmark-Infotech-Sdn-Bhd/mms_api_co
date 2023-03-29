<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['id' => 1, 'permission_name' => 'All', 'status' => 1]);
        Permission::create(['id' => 2, 'permission_name' => 'View', 'status' => 1]);
        Permission::create(['id' => 3, 'permission_name' => 'Add', 'status' => 1]);
        Permission::create(['id' => 4, 'permission_name' => 'Edit', 'status' => 1]);
        Permission::create(['id' => 5, 'permission_name' => 'Delete', 'status' => 1]);
        Permission::create(['id' => 6, 'permission_name' => 'Download', 'status' => 1]);
    }
}
