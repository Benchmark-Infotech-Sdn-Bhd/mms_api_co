<?php


namespace App\Services;

use App\Models\FomemaClinics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FomemaClinicsServices
{
    /**
     * @var fomemaClinics
     */
    private $fomemaClinics;

    public function __construct(FomemaClinics $fomemaClinics)
    {
        $this->fomemaClinics = $fomemaClinics;
    }
	 /**
     * Show the form for creating a new Fomema Clinics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create($request)
    {     
        $input = $request->all();
        $validation = $this->fomemaClinics::validate($input);
        if ($validation !== true) {
            return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
        }
        $fomemaClinicsData = $this->fomemaClinics::create([
            'clinic_name' => $request["clinic_name"],
            'person_in_charge' => $request["person_in_charge"],
            'pic_contact_number' => $request["pic_contact_number"],
            'address' => $request["address"],
            'state' => $request["state"],
            'city' => $request["city"],
            'postcode' => $request["postcode"],
        ]);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$fomemaClinicsData],200);
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @return JsonResponse
     */ 
    public function show()
    {
        $fomemaClinicsData = $this->fomemaClinics::paginate(10);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$fomemaClinicsData],200);
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $fomemaClinicsData = $this->fomemaClinics::findorfail($id);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$fomemaClinicsData],200);
        
    }
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateData($id, $request)
    {     
        try {
            $input = $request->all();
            $validation = $this->fomemaClinics::validate($input);
            if ($validation !== true) {
                return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
            }
            $data = $this->fomemaClinics::findorfail($id);
            $fomemaClinicsData = $data->update($request->all());
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$input],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return JsonResponse
     */    
    public function delete($id)
    {     
        try {
            $data = $this->fomemaClinics::findorfail($id);
            $data->delete();
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>''],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
}