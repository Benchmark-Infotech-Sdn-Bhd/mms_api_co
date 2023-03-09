<?php


namespace App\Services;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VendorServices
{
    /**
     * @var vendorServices
     */
    private $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
	 /**
     * Show the form for creating a new Vendor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create($request)
    {   
        $input = $request->all();
        $validation = $this->vendor::validate($input);
        if ($validation !== true) {
            return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
        }  
        $vendor = $this->vendor::create([
            'name' => $request["name"],
            'state' => $request["state"],
            'type' => $request["type"],
            'person_in_charge' => $request["person_in_charge"],
            'contact_number' => $request["contact_number"],
            'email_address' => $request["email_address"],
            'address' => $request["address"],
        ]);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$vendor],200);

        
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @return JsonResponse
     */
    public function show()
    {
        $vendors = $this->vendor::with('accommodations', 'insurances', 'transportations')->paginate(10);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$vendors],200);
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {    
        $vendors = $this->vendor::find($id);
        // $accommodations = $vendors->accommodations;
        // $insurances = $vendors->insurances;
        // $transportations = $vendors->transportations;
        // $vendors = Vendor::findorfail($id);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$vendors],200);
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateData($id, $request)
    {             
        try {
            $input = $request->all();
            $validation = $this->vendor::validate($input);
            if ($validation !== true) {
                return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
            } 
            $vendors = $this->vendor::findorfail($id);
            $data = $vendors->update($request->all());
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'ok','data'=>$input],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param $id
     * @return JsonResponse
     */    
    public function delete($id)
    {     
        try {
            // $data->delete();
            $vendors = $this->vendor::find($id);
            $vendors->accommodations()->delete();
            $vendors->insurances()->delete();
            $vendors->transportations()->delete();
            $vendors->delete();
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'deleted success','data'=>''],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }

}