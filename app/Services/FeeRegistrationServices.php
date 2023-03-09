<?php


namespace App\Services;

use App\Models\FeeRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeeRegistrationServices
{
    /**
     * @var feeRegistration
     */
    private $feeRegistration;

    public function __construct(FeeRegistration $feeRegistration)
    {
        $this->feeRegistration = $feeRegistration;
    }
    /**
     * Show the form for creating a new Fee Registration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create($request)
    {     
        $input = $request->all();
        $validation = $this->feeRegistration::validate($input);
        if ($validation !== true) {
            return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
        }
        $feeRegistrationData = $this->feeRegistration::create([
            'item' => $request["item"],
            'fee_per_pax' => $request["fee_per_pax"],
            'type' => $request["type"],
            'applicable_for' => $request["applicable_for"],
            'sectors' => $request["sectors"],
        ]);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$feeRegistrationData],200);
    }
    /**
     * Display a listing of the Fee Registration data.
     *
     * @return JsonResponse
     */
    public function show()
    {
        $feeRegistrationData = $this->feeRegistration::paginate(10);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$feeRegistrationData],200);
    }
    /**
     * Display the data for edit form by using feeRegistration id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $feeRegistrationData = $this->feeRegistration::findorfail($id);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$feeRegistrationData],200);
    }
	 /**
     * Update the specified Fee Registration data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateData($id, $request)
    {     
        try {
            $input = $request->all();
            $validation = $this->feeRegistration::validate($input);
            if ($validation !== true) {
                return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
            }
            $data = $this->feeRegistration::findorfail($id);
            $feeRegistration = $data->update($request->all());
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$input],200);
            
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param $id
     * @return JsonResponse
     */    
    public function delete($id)
    {     
        try {
            $data = $this->feeRegistration::findorfail($id);
            $data->delete();
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>''],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
}