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
        RenewalNotification::create(['id' => 1, 'notification_name' => 'FOMEMA Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 2, 'notification_name' => 'Passport Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 3, 'notification_name' => 'PLKS Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 4, 'notification_name' => 'Calling Visa Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 5, 'notification_name' => 'Special Passes Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 6, 'notification_name' => 'Insurance Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 7, 'notification_name' => 'Entry Visa Renewal', 'status' => 1]);
        RenewalNotification::create(['id' => 8, 'notification_name' => 'Service Agreement Renewal', 'status' => 1]);
    }
}
