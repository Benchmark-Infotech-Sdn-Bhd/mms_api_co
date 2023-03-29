<?php


namespace App\Services;

use App\Models\Insurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InsuranceServices
{
    /**
     * @var Insurance
     */
    private Insurance $insurance;

    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if(!($this->insurance->validate($request->all()))){
            return $this->insurance->errors();
        }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->insurance->validateUpdation($request->all()))){
            return $this->insurance->errors();
        }
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        return $this->insurance::create([
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
        ]);
    }
    /**
     * @param $request
     * @return LengthAwarePaginator
     */
    public function retrieveAll($request)
    {
        return $this->insurance::with('vendor')->find($request['vendor_id'])->orderBy('insurance.created_at','DESC')->paginate(10);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        return $this->insurance::findorfail($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {           
        $data = $this->insurance::findorfail($request['id']);
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
    public function delete($request) : mixed
    {     
        $data = $this->insurance::find($request['id']);
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
        return $this->insurance->where('no_of_worker_from', 'like', '%' . $request->search . '%')
        ->orWhere('no_of_worker_to', 'like', '%' . $request->search . '%')
        ->orWhere('fee_per_pax', 'like', '%' . $request->search . '%')
        ->orderBy('insurance.created_at','DESC')
        ->paginate(10);
    }
}