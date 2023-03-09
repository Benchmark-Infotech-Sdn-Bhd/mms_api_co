<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FomemaClinicsServices;
use App\Models\FomemaClinics;
use Illuminate\Support\Facades\Validator;

class FomemaClinicsController extends Controller
{
    /**
     * @var fomemaClinicsServices
     */
    private $fomemaClinicsServices;

    public function __construct(FomemaClinicsServices $fomemaClinicsServices)
    {
        $this->fomemaClinicsServices = $fomemaClinicsServices;
    }
	 /**
     * Show the form for creating a new Fomema Clinics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFomemaClinics(Request $request)
    {
        $response = $this->fomemaClinicsServices->create($request); 
        return $response;
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @return JsonResponse
     */    
    public function showFomemaClinics()
    {        
        $response = $this->fomemaClinicsServices->show(); 
        return $response;
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFomemaClinics($id)
    {   
        $response = $this->fomemaClinicsServices->edit($id); 
        return $response;
    } 
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFomemaClinics(Request $request, $id)
    {                
        $response = $this->fomemaClinicsServices->updateData($id, $request); 
        return $response;
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteFomemaClinics($id)
    {    
        $response = $this->fomemaClinicsServices->delete($id); 
        return $response;        
    }
    
}
