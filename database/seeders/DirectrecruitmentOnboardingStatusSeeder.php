<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DirectrecruitmentOnboardingStatus;

class DirectrecruitmentOnboardingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DirectrecruitmentOnboardingStatus::create(['id' => 1, 'name' => 'Pending', 'status' => 1]);
        DirectrecruitmentOnboardingStatus::create(['id' => 2, 'name' => 'Agent Added', 'status' => 1]);
        DirectrecruitmentOnboardingStatus::create(['id' => 3, 'name' => 'Attestation Submitted', 'status' => 1]);
        DirectrecruitmentOnboardingStatus::create(['id' => 4, 'name' => 'Workers Added', 'status' => 1]);
        DirectrecruitmentOnboardingStatus::create(['id' => 5, 'name' => 'Calling Visa Generated', 'status' => 1]);
        DirectrecruitmentOnboardingStatus::create(['id' => 6, 'name' => 'Calling Visa Generated', 'status' => 1]);
        DirectrecruitmentOnboardingStatus::create(['id' => 7, 'name' => 'Post Arrival Completed', 'status' => 1]);
    }
}
