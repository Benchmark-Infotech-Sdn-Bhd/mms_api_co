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
        return $this->feeRegistration::paginate(10);
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
        return $data->update($request->all());
    }
	 /**
     *
     * @param $request
     * @return void
     */    
    public function delete($request): void
    {     
        $data = $this->feeRegistration::find($request['id']);
        $data->delete();
    }

    /**
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