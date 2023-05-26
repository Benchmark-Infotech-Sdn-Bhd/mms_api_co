<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DirectRecruitmentApplicationStatus;

class DirectRecruitmentApplicationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DirectRecruitmentApplicationStatus::create(['id' => 1, 'status_name' => 'Pending Proposal', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 2, 'status_name' => 'Proposal Submitted', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 3, 'status_name' => 'Checklist Completed', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 4, 'status_name' => 'FWCMS Completed', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 5, 'status_name' => 'Interview Completed', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 6, 'status_name' => 'Levy Completed', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 7, 'status_name' => 'Approval Completed', 'status' => 1]);
        DirectRecruitmentApplicationStatus::create(['id' => 8, 'status_name' => 'FWCMS Rejected', 'status' => 1]);
    }
}
