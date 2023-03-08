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
    private $accommodationServices;
    private $accommodation;

    public function __construct(AccommodationServices $accommodationServices,Accommodation $accommodation)
    {
        $this->accommodationServices = $accommodationServices;
        $this->accommodation = $accommodation;
    }

    public function createAccommodation(Request $request)
    {
        $input = $request->all();
        $validation = $this->accommodation::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }

        if (request()->hasFile('attachment')){
            $uploadedImage = $request->file('attachment');
            $imageName = time() . '.' . $uploadedImage->getClientOriginalExtension();
            $destinationPath = storage_path('images');
            $uploadedImage->move($destinationPath, $imageName);
            $input['attachment'] = "images/".$imageName;
        }

        $response = $this->accommodationServices->create($input); 
        return $response;
    }
    
    public function showAccommodation()
    {        
        $response = $this->accommodationServices->show(); 
        return $response;
    }

    public function editAccommodation($id)
    {     
        $response = $this->accommodationServices->edit($id); 
        return $response; 
    } 

    public function updateAccommodation(Request $request, $id)
    {        
        
        $input = $request->all();
        
        $validation = $this->accommodation::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->accommodationServices->update($id, $request); 
        return $input;
    }

    public function deleteAccommodation($id)
    {   
        $response = $this->accommodationServices->delete($id); 
        return $response;        
    }

    public function searchAccommodation(Request $request)
    {
        $accommodation = $this->accommodation::where('accommodation_name', 'like', '%'.$request->name.'%')
             ->get();        
        return $accommodation;        
    }

}
