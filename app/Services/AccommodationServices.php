<?php


namespace App\Services;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccommodationServices
{
    private $accommodation;

    public function __construct(Accommodation $accommodation)
    {
        $this->accommodation = $accommodation;
    }

    public function create($request)
    {     
        $accommodation = $this->accommodation::create([
            'accommodation_name' => $request["accommodation_name"],
            'number_of_units' => $request["number_of_units"],
            'number_of_rooms' => $request["number_of_rooms"],
            'maximum_pax_per_room' => $request["maximum_pax_per_room"],
            'cost_per_pax' => $request["cost_per_pax"],
            'attachment' => $request["attachment"],
            'rent_deposit' => $request["rent_deposit"],
            'rent_per_month' => $request["rent_per_month"],
            'rent_advance' => $request["rent_advance"],
            'vendor_id' => $request["vendor_id"],
        ]);
        return response()->json($accommodation,200);
    }

    public function show()
    {
        $accommodationData = $this->accommodation::with('vendor')->paginate(10);
        return response()->json($accommodationData,200);
    }

    public function edit($id)
    {
        $accommodationData = $this->accommodation::findorfail($id);
        return response()->json($accommodationData,200);
    }

    public function update($id, $request)
    {     
        try {
            $data = $this->accommodation::findorfail($id);
            $input = $request->all();
            if (request()->hasFile('attachment')){
                $uploadedImage = $request->file('attachment');
                $imageName = time() . '.' . $uploadedImage->getClientOriginalExtension();
                $destinationPath = storage_path('images');
                $uploadedImage->move($destinationPath, $imageName);
                $input['attachment'] = "images/".$imageName;
            }
            $accommodationData = $data->update($input);
            return response()->json(['message' => 'Accommodation details updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Accommodation details update was failed'], 400);
        }
    }
    
    public function delete($id)
    {     
        try {
            $data = $this->accommodation::findorfail($id);
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Accommodation details delete was failed'], 400);
        }
    }

}