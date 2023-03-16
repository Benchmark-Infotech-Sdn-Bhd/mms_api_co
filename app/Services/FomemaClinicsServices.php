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
     *
     * @param $request
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
     *
     * @return LengthAwarePaginator
     */ 
    public function retrieveAll()
    {
        return $this->fomemaClinics::paginate(10);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        return $this->fomemaClinics::findorfail($request['id']);        
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {     
        $data = $this->fomemaClinics::findorfail($request['id']);
        return $data->update($request->all());
    }
	 /**
     *
     * @param $request
     * @return void
     */    
    public function delete($request): void
    {    
        $data = $this->fomemaClinics::find($request['id']);
        $data->delete();
    }
    /**
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