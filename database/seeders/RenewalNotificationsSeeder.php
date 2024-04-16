<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RenewalNotification;

class RenewalNotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RenewalNotification::create(['id' => 1, 'notification_name' => 'FOMEMA', 'status' => 1]);
        RenewalNotification::create(['id' => 2, 'notification_name' => 'Passport', 'status' => 1]);
        RenewalNotification::create(['id' => 3, 'notification_name' => 'PLKS', 'status' => 1]);
        RenewalNotification::create(['id' => 4, 'notification_name' => 'Calling Visa', 'status' => 1]);
        RenewalNotification::create(['id' => 5, 'notification_name' => 'Special Passes', 'status' => 1]);
        RenewalNotification::create(['id' => 6, 'notification_name' => 'Insurance', 'status' => 1]);
        RenewalNotification::create(['id' => 7, 'notification_name' => 'Entry Visa', 'status' => 1]);
        RenewalNotification::create(['id' => 8, 'notification_name' => 'Service Agreement', 'status' => 1]);
    }
}
