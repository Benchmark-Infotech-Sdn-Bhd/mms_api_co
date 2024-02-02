<?php

namespace App\Services;

use App\Models\Services;
use Illuminate\Support\Facades\Config;

class ServiceServices
{
    public const ACTIVE_STATUS = 1;

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
     * Lists services based on the company's subscription.
     *
     * @param array $request The request data.
     *   - company_id (int) The ID of the company.
     *
     * @return mixed The final result containing the services.
     */
    public function dropDown($request): mixed
    {
        return $this->services->join('modules', 'modules.id', 'services.module_id')
                ->join('company_module_permission', function ($join) use ($request) {
                    $join->on('company_module_permission.module_id', '=', 'modules.id')
                         ->where('company_module_permission.company_id', $request['company_id'])
                         ->whereNull('company_module_permission.deleted_at');
                })
                ->where('modules.status', self::ACTIVE_STATUS)
                ->where('services.status', self::ACTIVE_STATUS)
                ->select('services.id', 'services.service_name')
                ->get();
    }
}