<?php


namespace App\Services;

use App\Models\Insurance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InsuranceServices
{
    /**
     * @var Insurance
     */
    private $insurance;

    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
    }
	 /**
     * Show the form for creating a new Insurance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create($request)
    {     
        $input = $request->all();
        $validation = $this->insurance::validate($input);
        if ($validation !== true) {
            return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
        }
        $insuranceData = $this->insurance::create([
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$insuranceData],200);
    }
	 /**
     * Display a listing of the Insurance.
     *
     * @return JsonResponse
     */ 
    public function show()
    {
        $insuranceData = $this->insurance::with('vendor')->paginate(10);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$insuranceData],200);
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $insuranceData = $this->insurance::findorfail($id);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$insuranceData],200);
    }
	 /**
     * Update the specified Insurance data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateData($id, $request)
    {     
        try {
            $input = $request->all();
            $validation = $this->insurance::validate($input);
            if ($validation !== true) {
                return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
            }
            $data = $this->insurance::findorfail($id);
            $feeRegistration = $data->update($request->all());
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$input],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param $id
     * @return JsonResponse
     */    
    public function delete($id)
    {     
        try {
            $data = $this->insurance::findorfail($id);
            $data->delete();
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>''],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
}