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
     * @param $request
     * @return true or false
     */
    public function inputValidation($request)
    {
       $input = $request->all();
       $validation = $this->feeRegistration::validate($input);
       return $validation;
    }
    /**
     * Show the form for creating a new Fee Registration.
     *
     * @param Request $request
     * @return true 
     */
    public function create($request)
    {  
        $feeRegistrationData = $this->feeRegistration::create([
            'item' => $request["item"],
            'fee_per_pax' => $request["fee_per_pax"],
            'type' => $request["type"],
            'applicable_for' => $request["applicable_for"],
            'sectors' => $request["sectors"],
        ]);
        return true;
    }
    /**
     * Display a listing of the Fee Registration data.
     *
     * @return JsonResponse
     */
    public function show()
    {
        return $this->feeRegistration::paginate(10);
    }
    /**
     * Display the data for edit form by using feeRegistration id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        return $this->feeRegistration::findorfail($id);
    }
	 /**
     * Update the specified Fee Registration data.
     *
     * @param Request $request, $id
     * @return true or false
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
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param $id
     * @return true or false
     */    
    public function delete($id)
    {     
        try {
            $data = $this->feeRegistration::findorfail($id);
            $data->delete();
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
}