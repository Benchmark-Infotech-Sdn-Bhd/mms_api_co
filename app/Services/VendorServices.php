<?php


namespace App\Services;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VendorServices
{
    private $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function create($request)
    {     
        $vendor = $this->vendor::create([
            'name' => $request["name"],
            'state' => $request["state"],
            'type' => $request["type"],
            'person_in_charge' => $request["person_in_charge"],
        ]);
        return response()->json($vendor,200);
    }

    public function update($data, $request)
    {     
        try {
            $vendor = $data->update($request->all());
            return response()->json(['message' => 'Vendor updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Vendor update was failed'], 400);
        }
    }
    
    public function delete($data)
    {     
        try {
            // $data->delete();
            $data->accommodations()->delete();
            $data->insurances()->delete();
            $data->transportations()->delete();
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Vendor delete was failed'], 400);
        }
    }

}