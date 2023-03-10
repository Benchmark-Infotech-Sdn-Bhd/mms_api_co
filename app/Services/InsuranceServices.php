<?php


namespace App\Services;

use App\Models\Insurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Show the form for creating a new Insurance.
     *
     * @param Request $request
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
     * Display a listing of the Insurance.
     *
     * @return LengthAwarePaginator
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
     * @param $id
     * @param $request
     * @return mixed
     */
    public function updateData($id, $request): mixed
    {   
        $data = $this->insurance::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {     
        $data = $this->insurance::findorfail($id);
        $data->delete();
    }
    /**
     * searching Insurance data.
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->insurance->where('no_of_worker_from', 'like', '%' . $request->no_of_worker_from . '%')->get(['no_of_worker_from',
            'no_of_worker_to',
            'fee_per_pax',
            'vendor_id',
            'id']);
    }
}