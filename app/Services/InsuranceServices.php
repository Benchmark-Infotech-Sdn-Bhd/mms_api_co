<?php


namespace App\Services;

use App\Models\Insurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     *
     * @return LengthAwarePaginator
     */ 
    public function retrieveAll()
    {
        return $this->insurance::with('vendor')->paginate(10);
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
        return $data->update($request->all());
    }
	 /**
     *
     * @param $request
     * @return void
     */    
    public function delete($request) : void
    {     
        $data = $this->insurance::find($request['id']);
        $data->delete();
    }
    /**
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