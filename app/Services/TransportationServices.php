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
     * Show the form for creating a new Transportation.
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
     * Display a listing of the Transportation.
     *
     * @return LengthAwarePaginator
     */
    public function show()
    {
        return $this->transportation::with('vendor')->paginate(10);
    }
	 /**
     * Display the data for edit form by using Transportation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        return $this->transportation::findorfail($id);
    }
	 /**
     * Update the specified Transportation data.
     *
     * @param $id
     * @param $request
     * @return mixed
     */
    public function updateData($id, $request): mixed
    {     
        $data = $this->transportation::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified Transportation data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {     
        $data = $this->transportation::findorfail($id);
        $data->delete();
    }
    /**
     * searching transportation data.
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