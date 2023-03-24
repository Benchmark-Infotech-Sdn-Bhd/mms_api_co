<?php


namespace App\Services;

use App\Models\FeeRegistration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->feeRegistration->validateUpdation($request->all()))){
            return $this->feeRegistration->errors();
        }
    }
    /**
     *
     * @param $request
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
     *
     * @return LengthAwarePaginator
     */
    public function retrieveAll()
    {
        return $this->feeRegistration::orderBy('fee_registration.created_at','DESC')->paginate(10);
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        return $this->feeRegistration::findorfail($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {
        $data = $this->feeRegistration::findorfail($request['id']);
        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => "Updated Successfully"
        ];
    }
	 /**
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {     
        $data = $this->feeRegistration::find($request['id']);
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }

    /**
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    public function search($request)
    {
        return $this->feeRegistration->where('item_name', 'like', '%' . $request->search . '%')
        ->orWhere('fee_type', 'like', '%' . $request->search . '%')
        ->orWhere('applicable_for', 'like', '%' . $request->search . '%')
        ->orWhere('sectors', 'like', '%' . $request->search . '%')
        ->orderBy('fee_registration.created_at','DESC')
        ->paginate(10);
    }
}