<?php

namespace Database\Seeders;

use App\Models\CompanyModulePermission;
use Illuminate\Database\Seeder;

class unit_testing_user_company_modules extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyModulePermission::create(['id' => 1, 'company_id' => 1, 'module_id' => 1]);  
        CompanyModulePermission::create(['id' => 2, 'company_id' => 1, 'module_id' => 2]);  
        CompanyModulePermission::create(['id' => 3, 'company_id' => 1, 'module_id' => 3]);  
        CompanyModulePermission::create(['id' => 4, 'company_id' => 1, 'module_id' => 4]);  
        CompanyModulePermission::create(['id' => 5, 'company_id' => 1, 'module_id' => 5]);  
        CompanyModulePermission::create(['id' => 6, 'company_id' => 1, 'module_id' => 6]);  
        CompanyModulePermission::create(['id' => 7, 'company_id' => 1, 'module_id' => 7]);  
        CompanyModulePermission::create(['id' => 8, 'company_id' => 1, 'module_id' => 8]);  
        CompanyModulePermission::create(['id' => 9, 'company_id' => 1, 'module_id' => 9]);  
        CompanyModulePermission::create(['id' => 10, 'company_id' => 1, 'module_id' => 10]);  
        CompanyModulePermission::create(['id' => 11, 'company_id' => 1, 'module_id' => 11]);  
        CompanyModulePermission::create(['id' => 12, 'company_id' => 1, 'module_id' => 12]);  
        CompanyModulePermission::create(['id' => 13, 'company_id' => 1, 'module_id' => 13]);  
        CompanyModulePermission::create(['id' => 14, 'company_id' => 1, 'module_id' => 16]);  
    }
}
