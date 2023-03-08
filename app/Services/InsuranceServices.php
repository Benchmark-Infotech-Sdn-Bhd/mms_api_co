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
            'create_insurance' => $request["create_insurance"],
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return response()->json($insurance,200);
    }

    public function show()
    {
        $insuranceData = $this->insurance::with('vendor')->paginate(10);
        return response()->json($insuranceData,200);
    }

    public function edit($id)
    {
        $insuranceData = $this->insurance::findorfail($id);
        return response()->json($insuranceData,200);
    }

    public function updateData($id, $request)
    {     
        try {
            $data = $this->insurance::findorfail($id);
            $feeRegistration = $data->update($request->all());
            return response()->json(['message' => 'Insurance details updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Insurance details update was failed'], 400);
        }
    }
    
    public function delete($id)
    {     
        try {
            $data = $this->insurance::findorfail($id);
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Insurance details delete was failed'], 400);
        }
    }
}