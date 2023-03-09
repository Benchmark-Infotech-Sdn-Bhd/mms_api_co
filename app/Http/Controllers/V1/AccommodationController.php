<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\AccommodationServices;
use App\Models\Accommodation;
use Illuminate\Support\Facades\Validator;

class AccommodationController extends Controller
{
    /**
     * @var accommodationServices
     */
    private $accommodationServices;

    public function __construct(AccommodationServices $accommodationServices)
    {
        $this->accommodationServices = $accommodationServices;
    }

    /**
     * Show the form for creating a new Accommodation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAccommodation(Request $request)
    {
        $response = $this->accommodationServices->create($request); 
        return $response;
    }
    
    /**
     * Display a listing of the Accommodation.
     *
     * @return JsonResponse
     */
    public function showAccommodation()
    {        
        $response = $this->accommodationServices->show(); 
        return $response;
    }

    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editAccommodation($id)
    {     
        $response = $this->accommodationServices->edit($id); 
        return $response; 
    } 
    /**
     * Update the specified Accommodation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateAccommodation(Request $request, $id)
    {  
        $response = $this->accommodationServices->update($id, $request); 
        return $response;
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteAccommodation($id)
    {   
        $response = $this->accommodationServices->delete($id); 
        return $response;        
    }
    /**
     * searching Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAccommodation(Request $request)
    {          
        $response = $this->accommodationServices->search($request); 
        return $response; 
        
    }

}
