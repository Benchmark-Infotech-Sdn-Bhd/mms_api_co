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
     * @param $request
     * @return true or false
     */
    public function inputValidation($request)
    {
       $input = $request->all();
       $validation = $this->insurance::validate($input);
       return $validation;
    }
	 /**
     * Show the form for creating a new Insurance.
     *
     * @param Request $request
     * @return true
     */
    public function create($request)
    {   
        $insuranceData = $this->insurance::create([
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return true;
    }
	 /**
     * Display a listing of the Insurance.
     *
     * @return JsonResponse
     */ 
    public function show()
    {
        return $this->insurance::with('vendor')->paginate(10);
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        return $this->insurance::findorfail($id);
    }
	 /**
     * Update the specified Insurance data.
     *
     * @param Request $request, $id
     * @return true or false
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
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param $id
     * @return true or false
     */    
    public function delete($id)
    {     
        try {
            $data = $this->insurance::findorfail($id);
            $data->delete();
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
}