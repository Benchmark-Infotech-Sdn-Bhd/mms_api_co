<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\MaintainMastersServices;

class MaintainMastersController extends Controller
{
    /**
     * @var MaintainMastersServices
     */
    private $maintainMastersServices;

    /**
     * MaintainMastersController constructor.
     * @param MaintainMastersServices $maintainMastersServices
     */
    public function __construct(MaintainMastersServices $maintainMastersServices)
    {
        $this->maintainMastersServices = $maintainMastersServices;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return $this->maintainMastersServices->create($request->all());
    }
}
