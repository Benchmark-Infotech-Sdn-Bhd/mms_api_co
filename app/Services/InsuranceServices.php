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
     * @return JsonResponse
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
    public function create($request)
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
     * @return bool
     */
    public function updateData($id, $request)
    {   
        $data = $this->insurance::findorfail($id);
        return $data->update($request->all());
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param $id
     * @return bool
     */    
    public function delete($id)
    {     
        $data = $this->insurance::findorfail($id);
        $data->delete();
    }
}