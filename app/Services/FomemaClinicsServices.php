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
     * @param $request
     * @return true or false
     */
    public function inputValidation($request)
    {
       $input = $request->all();
       $validation = $this->fomemaClinics::validate($input);
       return $validation;
    }
	 /**
     * Show the form for creating a new Fomema Clinics.
     *
     * @param Request $request
     * @return true
     */
    public function create($request)
    { 
        $fomemaClinicsData = $this->fomemaClinics::create([
            'clinic_name' => $request["clinic_name"],
            'person_in_charge' => $request["person_in_charge"],
            'pic_contact_number' => $request["pic_contact_number"],
            'address' => $request["address"],
            'state' => $request["state"],
            'city' => $request["city"],
            'postcode' => $request["postcode"],
        ]);
        return true;
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @return JsonResponse
     */ 
    public function show()
    {
        return $this->fomemaClinics::paginate(10);
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        return $this->fomemaClinics::findorfail($id);        
    }
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request, $id
     * @return true or false
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
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return true or false
     */    
    public function delete($id)
    {     
        try {
            $data = $this->fomemaClinics::findorfail($id);
            $data->delete();
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
}