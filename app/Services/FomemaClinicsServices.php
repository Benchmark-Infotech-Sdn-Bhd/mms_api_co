<?php


namespace App\Services;

use App\Models\FomemaClinics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FomemaClinicsServices
{
    private $fomemaClinics;

    public function __construct(FomemaClinics $fomemaClinics)
    {
        $this->fomemaClinics = $fomemaClinics;
    }

    public function create($request)
    {     
        $fomemaClinics = $this->fomemaClinics::create([
            'clinic_name' => $request["clinic_name"],
            'person_in_charge' => $request["person_in_charge"],
            'pic_contact_number' => $request["pic_contact_number"],
            'address' => $request["address"],
            'state' => $request["state"],
            'city' => $request["city"],
            'postcode' => $request["postcode"],
        ]);
        return response()->json($fomemaClinics,200);
    }

    public function show()
    {
        $fomemaClinicsData = $this->fomemaClinics::paginate(10);
        return response()->json($fomemaClinicsData,200);
    }

    public function edit($id)
    {
        $fomemaClinicsData = $this->fomemaClinics::findorfail($id);
        return response()->json($fomemaClinicsData,200);
    }

    public function updateData($id, $request)
    {     
        try {
            $data = $this->fomemaClinics::findorfail($id);
            $fomemaClinicsData = $data->update($request->all());
            return response()->json(['message' => 'FOMEMAClinic updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'FOMEMAClinic update was failed'], 400);
        }
    }
    
    public function delete($id)
    {     
        try {
            $data = $this->fomemaClinics::findorfail($id);
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'FOMEMAClinic delete was failed'], 400);
        }
    }
}