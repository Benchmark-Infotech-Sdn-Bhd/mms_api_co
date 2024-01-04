<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KinRelationship;

class KinRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KinRelationship::create(['id' => 1, 'name' => 'Parents']);
        KinRelationship::create(['id' => 2, 'name' => 'Children']);
        KinRelationship::create(['id' => 3, 'name' => 'Brothers']);
        KinRelationship::create(['id' => 4, 'name' => 'Sisters']);
        KinRelationship::create(['id' => 5, 'name' => 'Grandparents']);
        KinRelationship::create(['id' => 6, 'name' => 'Uncles']);
        KinRelationship::create(['id' => 7, 'name' => 'Aunts']);
        KinRelationship::create(['id' => 8, 'name' => 'Others']);
    }
}
