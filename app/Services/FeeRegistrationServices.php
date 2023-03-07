<?php


namespace App\Services;

use App\Models\FeeRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeeRegistrationServices
{
    private $feeRegistration;

    public function __construct(FeeRegistration $feeRegistration)
    {
        $this->feeRegistration = $feeRegistration;
    }

    public function create($request)
    {     
        $feeRegistration = $this->feeRegistration::create([
            'item' => $request["item"],
            'fee_per_pax' => $request["fee_per_pax"],
            'type' => $request["type"],
            'applicable_for' => $request["applicable_for"],
            'sectors' => $request["sectors"],
        ]);
        return response()->json($feeRegistration,200);
    }

    public function update($data, $request)
    {     
        try {
            $feeRegistration = $data->update($request->all());
            return response()->json(['message' => 'Fee details updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Fee details update was failed'], 400);
        }
    }
    
    public function delete($data)
    {     
        try {
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Fee details delete was failed'], 400);
        }
    }
}