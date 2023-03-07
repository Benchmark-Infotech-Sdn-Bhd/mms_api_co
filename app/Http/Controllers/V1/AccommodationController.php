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

    public function __construct(AccommodationServices $accommodationServices,)
    {
        $this->accommodationServices = $accommodationServices;
    }

    public function createAccommodation(Request $request)
    {
        $input = $request->all();
        $validation = Accommodation::validate($input);
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
        // $accommodation = Accommodation::paginate(10);
        $accommodation = Accommodation::with('vendor')->paginate(10);
        return response()->json($accommodation,200);
    }

    public function editAccommodation($id)
    {        
        $accommodation = Accommodation::findorfail($id);
        // $accommodation = Accommodation::find($id);
        // $vendors = $accommodation->vendor;
        return response()->json($accommodation,200);
    } 

    public function updateAccommodation(Request $request, $id)
    {        
        $accommodation = Accommodation::findorfail($id);
        $input = $this->getRequest($request);
        
        $validation = Accommodation::validate($input);
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

        $response = $this->accommodationServices->update($accommodation, $input); 
        return $response;
    }

    public function deleteAccommodation($id)
    {       
        $accommodation = Accommodation::findorfail($id);
        $response = $this->accommodationServices->delete($accommodation); 
        return $response;
        
    }

    public function searchAccommodation(Request $request)
    {
        $accommodation = Accommodation::where('accommodation_name', 'like', '%'.$request->name.'%')
             ->get();        
        return $accommodation;        
    }

}
