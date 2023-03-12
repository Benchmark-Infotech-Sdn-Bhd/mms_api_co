<?php


namespace App\Services;

use App\Models\FeeRegistration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeRegistrationServices
{
    /**
     * @var feeRegistration
     */
    private FeeRegistration $feeRegistration;

    public function __construct(FeeRegistration $feeRegistration)
    {
        $this->feeRegistration = $feeRegistration;
    }
    /**
     * @param $request
     * @return mixed | void
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
    public function create($request): mixed
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
     * @return LengthAwarePaginator
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
     * @param $id
     * @param $request
     * @return mixed
     */
    public function updateData($id, $request): mixed
    {
        $data = $this->feeRegistration::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {     
        $data = $this->feeRegistration::findorfail($id);
        $data->delete();
    }

    /**
     * searching Fee Registration data.
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->feeRegistration->where('item_name', 'like', '%' . $request->item_name . '%')->get(
            ['id',
            'item_name',
            'cost',
            'fee_type',
            'applicable_for',
            'sectors']
        );
    }
}