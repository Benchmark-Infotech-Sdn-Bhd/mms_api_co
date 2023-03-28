<?php

namespace App\Services;

use App\Models\Services;
use Illuminate\Support\Facades\Config;

class ServiceServices
{
    /**
     * @var Services
     */
    private $services;

    /**
     * ServiceServices constructor.
     * @param Services $services
     */
    public function __construct(Services $services)
    {
        $this->services = $services;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->services->where('status', 1)
                ->where(function ($query) use ($request) {
                    if(isset($request['search']) && !empty($request['search'])) {
                        $query->where('service_name', 'like', '%'.$request['search'].'%');
                    }
                })
                ->select('id', 'service_name', 'status')
                ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @return mixed
     */
    public function dropDown(): mixed
    {
        return $this->services->where('status', 1)
                ->select('id', 'service_name')
                ->get();
    }
}