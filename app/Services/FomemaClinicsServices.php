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
     * @return JsonResponse
     */
    public function inputValidation($request)
    {
        if(!($this->fomemaClinics->validate($request->all()))){
            return $this->fomemaClinics->errors();
        }
    }
	 /**
     * Show the form for creating a new Fomema Clinics.
     *
     * @param Request $request
     * @return mixed
     */
    public function create($request)
    { 
        return $this->fomemaClinics::create([
            'clinic_name' => $request["clinic_name"],
            'person_in_charge' => $request["person_in_charge"],
            'pic_contact_number' => $request["pic_contact_number"],
            'address' => $request["address"],
            'state' => $request["state"],
            'city' => $request["city"],
            'postcode' => $request["postcode"],
        ]);
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
     * @return bool
     */
    public function updateData($id, $request)
    {     
        $data = $this->fomemaClinics::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return bool
     */    
    public function delete($id)
    {    
        $data = $this->fomemaClinics::findorfail($id);
        $data->delete();
    }
}