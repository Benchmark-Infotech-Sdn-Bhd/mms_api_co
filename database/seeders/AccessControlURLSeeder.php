<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccessControlURL;

class AccessControlURLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DirectRecruitmentApplicationStatus::create(['id' => 1, 'module_id' => 1, 'module_name' => 'Dashboard', 'url' => 'api/v1/dashboard', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 2, 'module_id' => 2, 'module_name' => 'Maintain Masters', 'url' => 'api/v1/dashboard', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 3, 'module_id' => 3, 'module_name' => 'Branches', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 4, 'module_id' => 4, 'module_name' => 'CRM', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 5, 'module_id' => 5, 'module_name' => 'Direct Recruitment', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 6, 'module_id' => 6, 'module_name' => 'e-Contract', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 7, 'module_id' => 7, 'module_name' => 'Total Management', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 8, 'module_id' => 8, 'module_name' => 'Employee', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 9, 'module_id' => 9, 'module_name' => 'Access Management', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 10, 'module_id' => 10, 'module_name' => 'Workers', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 11, 'module_id' => 11, 'module_name' => 'Dispatch Management', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 12, 'module_id' => 12, 'module_name' => 'Invoice', 'url' => '', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 13, 'module_id' => 13, 'module_name' => 'Reports', 'url' => '', 'status' => 1]);
    }
}
