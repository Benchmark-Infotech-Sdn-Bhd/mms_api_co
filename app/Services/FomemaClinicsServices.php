<?php


namespace App\Services;

use App\Models\FomemaClinics;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FomemaClinicsServices
{
    /**
     * @var fomemaClinics
     */
    private FomemaClinics $fomemaClinics;

    public function __construct(FomemaClinics $fomemaClinics)
    {
        $this->fomemaClinics = $fomemaClinics;
    }
    /**
     * @param $request
     * @return mixed | void
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
    public function create($request): mixed
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
     * @return LengthAwarePaginator
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
     * @param $id
     * @param $request
     * @return mixed
     */
    public function updateData($id, $request): mixed
    {     
        $data = $this->fomemaClinics::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {    
        $data = $this->fomemaClinics::findorfail($id);
        $data->delete();
    }
    /**
     * searching FOMEMA Clinics data.
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->fomemaClinics->where('clinic_name', 'like', '%' . $request->clinic_name . '%')->get(['clinic_name',
            'person_in_charge',
            'pic_contact_number',
            'address',
            'state',
            'city',
            'postcode',
            'id']);
    }
}