<?php


namespace App\Services;

use App\Models\Transportation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportationServices
{
    /**
     * @var transportation
     */
    private Transportation $transportation;

    public function __construct(Transportation $transportation)
    {
        $this->transportation = $transportation;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
       if(!($this->transportation->validate($request->all()))){
           return $this->transportation->errors();
       }
    }
	 /**
     *
     * @param Request $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        return $this->transportation::create([
            'driver_name' => $request["driver_name"],
            'driver_contact_number' => $request["driver_contact_number"],
            'driver_license_number' => $request["driver_license_number"],
            'vehicle_type' => $request["vehicle_type"],
            'number_plate' => $request["number_plate"],
            'vehicle_capacity' => $request["vehicle_capacity"],
            'vendor_id' => $request["vendor_id"],
        ]);
    }
	 /**
     *
     * @return LengthAwarePaginator
     */
    public function retrieveAll()
    {
        return $this->transportation::with('vendor')->paginate(10);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        return $this->transportation::findorfail($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {     
        $data = $this->transportation::findorfail($request['id']);
        return $data->update($request->all());
    }
	 /**
     *
     * @param $request
     * @return void
     */    
    public function delete($request): void
    {     
        $data = $this->transportation::findorfail($request['id']);
        $data->delete();
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->transportation->where('driver_name', 'like', '%' . $request->driver_name . '%')->get(['driver_name',
            'driver_contact_number',
            'driver_license_number',
            'vehicle_type',
            'number_plate',
            'vehicle_capacity',
            'vendor_id',
            'id']);
    }
}