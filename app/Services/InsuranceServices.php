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
    private $Insurance;

    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
    }

    public function create($request)
    {     
        $insurance = $this->insurance::create([
            'create_insurance' => $request["no_of_worker_from"],
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return response()->json($insurance,200);
    }

    public function update($data, $request)
    {     
        try {
            $feeRegistration = $data->update($request->all());
            return response()->json(['message' => 'Insurance details updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Insurance details update was failed'], 400);
        }
    }
    
    public function delete($data)
    {     
        try {
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Insurance details delete was failed'], 400);
        }
    }
}