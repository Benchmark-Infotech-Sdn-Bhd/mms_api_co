<?php

namespace App\Services;

use App\Models\Audits;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'from_date' => 'required|date|date_format:Y-m-d',
            'to_date' => 'required|date|date_format:Y-m-d',
        ];
    }


    /**
     * @param $request
     * @return mixed
     */
    public function list($request) 
    {
        if(isset($request['from_date']) && !empty($request['from_date']) && isset($request['to_date']) && !empty($request['to_date'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        $request['from'] = Carbon::now()->subDays(10)->startOfDay();
        $request['to'] = Carbon::now()->endOfDay();

        if(isset($request['from_date']) && !empty($request['from_date'])){
            $request['from'] = Carbon::parse($request['from_date'])->startOfDay();
        }
        if(isset($request['to_date']) && !empty($request['to_date'])){
            $request['to'] = Carbon::parse($request['to_date'])->endOfDay();
        }
        
        $data = $this->audits->with(['user' => function ($query) {
            $query->select(['id', 'name']);
        }])
            ->select('id', 'user_type', 'user_id', 'event', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'url', 'created_at', 'updated_at')
            ->where(function ($query) use ($request) {
                if(isset($request['from']) && isset($request['to'])) {
                    $query->whereBetween('created_at', array($request['from'], $request['to']));
                }
            })
            ->orderBy('id', 'desc');
            if(isset($request['export']) && !empty($request['export']) ){
                $data = $data->get();
            }else{
                $data = $data->paginate(Config::get('services.paginate_worker_row'));
            }   
            
        return $data;
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