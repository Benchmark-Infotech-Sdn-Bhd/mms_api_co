<?php

namespace App\Services;

use App\Models\Audits;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class AuditsServices
{
    /**
     * @var audits
     */
    private Audits $audits;

    /**
     * AuditsServices constructor.
     * @param Audits $audits
     */
    public function __construct(Audits $audits)
    {
        $this->audits   = $audits;
    }


    /**
     * @param $request
     * @return mixed
     */
    public function list($request) 
    {
        $request['from'] = Carbon::now()->subWeek()->toDateTimeString();
        $request['to'] = Carbon::now()->toDateTimeString();

        if(isset($request['from_date']) && !empty($request['from_date'])){
            $request['from'] = Carbon::parse($request['from_date'])->toDateTimeString();
        }
        if(isset($request['to_date']) && !empty($request['to_date'])){
            $request['to'] = Carbon::parse($request['to_date'])->toDateTimeString();
        }
        
        return $this->audits
            ->select('id', 'user_type', 'user_id', 'event', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'url', 'ip_address', 'user_agent', 'tags', 'created_at')
            ->where(function ($query) use ($request) {
                if(isset($request['from']) && isset($request['to'])) {
                    $query->whereBetween('created_at', array($request['from'], $request['to']));
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete() : mixed
    {
        try {
            $conditionDate = Carbon::now()->subYear(3)->toDateTimeString();
            $data = $this->audits->where('created_at', '<=', $conditionDate)->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Exception in delete' . $e);
            return false;
        }
    }
}