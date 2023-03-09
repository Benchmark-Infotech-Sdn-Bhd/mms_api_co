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
     * @return JsonResponse
     */
    public function inputValidation($request)
    {
        if(!($this->feeRegistration->validate($request->all()))){
            return $this->feeRegistration->errors();
        }
    }
    /**
     * Show the form for creating a new Fee Registration.
     *
     * @param Request $request
     * @return mixed 
     */
    public function create($request)
    {  
        return $this->feeRegistration::create([
            'item_name' => $request["item_name"],
            'cost' => $request["cost"],
            'fee_type' => $request["fee_type"],
            'applicable_for' => $request["applicable_for"],
            'sectors' => $request["sectors"],
        ]);
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
     * @return bool
     */
    public function updateData($id, $request)
    {
        $data = $this->feeRegistration::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param $id
     * @return bool
     */    
    public function delete($id)
    {     
        $data = $this->feeRegistration::findorfail($id);
        $data->delete();
    }
}