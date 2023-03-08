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

    public function show()
    {
        $vendors = $this->vendor::with('accommodations', 'insurances', 'transportations')->paginate(10);
        return response()->json($vendors,200);
    }

    public function edit($id)
    {    
        $vendors = $this->vendor::find($id);
        // $accommodations = $vendors->accommodations;
        // $insurances = $vendors->insurances;
        // $transportations = $vendors->transportations;
        // $vendors = Vendor::findorfail($id);
        return response()->json($vendors,200);
    } 

    public function updateData($id, $request)
    {     
        
        try {
            $vendors = $this->vendor::findorfail($id);
            $data = $vendors->update($request->all());
            return response()->json(['message' => 'Vendor updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Vendor update was failed'], 400);
        }
    }
    
    public function delete($id)
    {     
        try {
            // $data->delete();
            $vendors = $this->vendor::find($id);
            $vendors->accommodations()->delete();
            $vendors->insurances()->delete();
            $vendors->transportations()->delete();
            $vendors->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Vendor delete was failed'], 400);
        }
    }

}