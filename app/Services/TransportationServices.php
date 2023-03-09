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
     * Show the form for creating a new Transportation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create($request)
    {     
        $input = $request->all();
        $validation = $this->transportation::validate($input);
        if ($validation !== true) {
            return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
        }
        $transportationData = $this->transportation::create([
            'driver_name' => $request["driver_name"],
            'driver_contact_number' => $request["driver_contact_number"],
            'driver_license_number' => $request["driver_license_number"],
            'vehicle_type' => $request["vehicle_type"],
            'number_plate' => $request["number_plate"],
            'vehicle_capacity' => $request["vehicle_capacity"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$transportationData],200);
    }
	 /**
     * Display a listing of the Transportation.
     *
     * @return JsonResponse
     */
    public function show()
    {
        $transportationData = $this->transportation::with('vendor')->paginate(10);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$transportationData],200);
    }
	 /**
     * Display the data for edit form by using Transportation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $transportationData = $this->transportation::findorfail($id);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$transportationData],200);
    }
	 /**
     * Update the specified Transportation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateData($id, $request)
    {     
        try {
            $input = $request->all();
            $validation = $this->transportation::validate($input);
            if ($validation !== true) {
                return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
            }
            $data = $this->transportation::findorfail($id);
            $transportationData = $data->update($request->all());
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$input],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
	 /**
     * delete the specified Transportation data.
     *
     * @param $id
     * @return JsonResponse
     */    
    public function delete($id)
    {     
        try {
            $data = $this->transportation::findorfail($id);
            $data->delete();
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>''],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
}