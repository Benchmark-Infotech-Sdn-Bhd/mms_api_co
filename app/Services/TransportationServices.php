<?php


namespace App\Services;

use App\Models\Transportation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransportationServices
{
    private $transportation;

    public function __construct(Transportation $transportation)
    {
        $this->transportation = $transportation;
    }

    public function create($request)
    {     
        $transportation = $this->transportation::create([
            'driver_name' => $request["driver_name"],
            'driver_contact_number' => $request["driver_contact_number"],
            'driver_license_number' => $request["driver_license_number"],
            'vehicle_type' => $request["vehicle_type"],
            'number_plate' => $request["number_plate"],
            'vehicle_capacity' => $request["vehicle_capacity"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return response()->json($transportation,200);
    }

    public function update($data, $request)
    {     
        try {
            $transportation = $data->update($request->all());
            return response()->json(['message' => 'Transportation details updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Transportation details update was failed'], 400);
        }
    }
    
    public function delete($data)
    {     
        try {
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Transportation details delete was failed'], 400);
        }
    }
}