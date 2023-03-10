<?php


namespace App\Services;

use App\Models\Transportation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransportationServices
{
    /**
     * @var transportation
     */
    private $transportation;

    public function __construct(Transportation $transportation)
    {
        $this->transportation = $transportation;
    }
	 /**
     * @param $request
     * @return JsonResponse
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
    public function create($request)
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
     * @return JsonResponse
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
     * @param Request $request, $id
     * @return bool
     */
    public function updateData($id, $request)
    {     
        $data = $this->transportation::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified Transportation data.
     *
     * @param $id
     * @return bool
     */    
    public function delete($id)
    {     
        $data = $this->transportation::findorfail($id);
        $data->delete();
    }
}