<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FeeRegistrationServices;
use App\Models\FeeRegistration;
use Illuminate\Support\Facades\Validator;

class FeeRegistrationController extends Controller
{
    /**
     * @var feeRegistrationServices
     */
    private $feeRegistrationServices;

    public function __construct(FeeRegistrationServices $feeRegistrationServices)
    {
        $this->feeRegistrationServices = $feeRegistrationServices;
    }

    /**
     * Show the form for creating a new Fee Registration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFeeRegistration(Request $request)
    {
        $response = $this->feeRegistrationServices->create($request); 
        return $response;
    }
    /**
     * Display a listing of the Fee Registration data.
     *
     * @return JsonResponse
     */
    public function showFeeRegistration()
    {        
        $response = $this->feeRegistrationServices->show(); 
        return $response;
    }
    /**
     * Display the data for edit form by using feeRegistration id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFeeRegistration($id)
    {    
        $response = $this->feeRegistrationServices->edit($id); 
        return $response;
    } 
	 /**
     * Update the specified Fee Registration data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFeeRegistration(Request $request, $id)
    {                
        $response = $this->feeRegistrationServices->updateData($id, $request); 
        return $response;
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteFeeRegistration($id)
    {      
        $response = $this->feeRegistrationServices->delete($id); 
        return $response;   
        
    }
    
}
