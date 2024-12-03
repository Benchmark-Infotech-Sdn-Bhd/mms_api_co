<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserDeactivationServices
{
    /**
     * Inactivate users who meet the criteria.
     *
     * @return bool
     */
    public function inactivateUsers(): bool
    { 
        try {
            $cutoffDate = Carbon::now()->subDays(30)->toDateTimeString(); // 30 days cutoff
            User::where('created_at', '<=', $cutoffDate)
                                    ->where('status', '1')
                                    ->update(['status' => '0']);
            Log::info('Query executed successfully.');
            return true;
        } catch (Exception $e) {
            Log::error('Exception in update' . $e);
            return false;
        }
    }
}
