<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        State::create(['id' => 1, 'state' => 'Perlis']);
        State::create(['id' => 2, 'state' => 'Kedah']);
        State::create(['id' => 3, 'state' => 'Penang']);
        State::create(['id' => 4, 'state' => 'Perak']);
        State::create(['id' => 5, 'state' => 'Selangor']);
        State::create(['id' => 6, 'state' => 'Negeri Sembilan']);
        State::create(['id' => 7, 'state' => 'Melaka']);
        State::create(['id' => 8, 'state' => 'Johor']);
        State::create(['id' => 9, 'state' => 'Kelantan']);
        State::create(['id' => 10, 'state' => 'Terengganu']);
        State::create(['id' => 11, 'state' => 'Pahang']);
        State::create(['id' => 12, 'state' => 'Sabah']);
        State::create(['id' => 13, 'state' => 'Sarawak']);
        State::create(['id' => 14, 'state' => 'Labuan']);
        State::create(['id' => 15, 'state' => 'Kuala Lumpur']);
    }
}
