<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompanyServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class CompanyController extends Controller
{
    /**
     * @var CompanyServices
     */
    private CompanyServices $companyServices;

    /**
     * CompanyController constructor
     * @param CompanyServices $companyServices
     */
    public function __construct(CompanyServices $companyServices)
    {
        $this->companyServices = $companyServices;
    }
    /**
     * Display the list of companies
     * 
     * @param Request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {

    }
}
