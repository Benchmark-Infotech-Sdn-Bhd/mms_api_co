<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\TransportationServices;
use App\Models\Transportation;
use Illuminate\Support\Facades\Validator;

class TransportationController extends Controller
{
    /**
     * @var transportationServices
     */
    private $transportationServices;

    public function __construct(TransportationServices $transportationServices)
    {
        $this->transportationServices = $transportationServices;
    }
	 /**
     * Show the form for creating a new Transportation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createTransportation(Request $request)
    {
        $response = $this->transportationServices->create($request); 
        return $response;
    }
	 /**
     * Display a listing of the Transportation.
     *
     * @return JsonResponse
     */    
    public function showTransportation()
    {        
        // $transportation = Transportation::paginate(10);
        $response = $this->transportationServices->show(); 
        return $response;  
    }
	 /**
     * Display the data for edit form by using Transportation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editTransportation($id)
    {      
        $response = $this->transportationServices->edit($id); 
        return $response;  
    } 
	 /**
     * Update the specified Transportation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateTransportation(Request $request, $id)
    {        
        $response = $this->transportationServices->updateData($id, $request); 
        return $response;
    }
	 /**
     * delete the specified Transportation data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteTransportation($id)
    {       
        $response = $this->transportationServices->delete($id); 
        return $response; 
    }
}
