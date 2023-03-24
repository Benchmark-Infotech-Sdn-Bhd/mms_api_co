<?php


namespace App\Services;

use App\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BranchServices
{
    /**
     * @var branch
     */
    private Branch $branch;

    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if(!($this->branch->validate($request->all()))){
            return $this->branch->errors();
        }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->branch->validateUpdation($request->all()))){
            return $this->branch->errors();
        }
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        return $this->branch::create([
            'branch_name' => $request["branch_name"],
            'state' => $request["state"],
            'city' => $request["city"],
            'branch_address' => $request["branch_address"],
            'service_type' => $request["service_type"],
            'postcode' => $request["postcode"],
            'remarks' => $request["remarks"],
        ]);
    }
	 /**
     *
     * @return LengthAwarePaginator
     */ 
    public function retrieveAll()
    {
        return $this->branch::orderBy('branch.created_at','DESC')->paginate(10);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        return $this->branch::findorfail($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {           
        $data = $this->branch::findorfail($request['id']);
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
        $data = $this->branch::find($request['id']);
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
        return $this->branch->where('branch_name', 'like', '%' . $request->search . '%')
        ->orWhere('state', 'like', '%' . $request->search . '%')
        ->orWhere('city', 'like', '%' . $request->search . '%')
        ->orderBy('branch.created_at','DESC')
        ->paginate(10);
    }
}