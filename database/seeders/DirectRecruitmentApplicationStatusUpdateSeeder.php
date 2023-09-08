<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DirectRecruitmentApplicationStatus;

class DirectRecruitmentApplicationStatusUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DirectRecruitmentApplicationStatus::updateOrCreate(['id' => 5, 'status_name' => 'Interview Approved', 'status' => 1]);
    }
}
