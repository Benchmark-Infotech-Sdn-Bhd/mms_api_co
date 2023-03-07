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

    public function update($data, $request)
    {     
        try {
            $accommodation = $data->update($request);
            return response()->json(['message' => 'Accommodation details updated successfully'],200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Accommodation details update was failed'], 400);
        }
    }
    
    public function delete($data)
    {     
        try {
            $data->delete();
            return response()->json('deleted success',200);
    
        } catch (Exception $exception) {
            return response()->json(['message' => 'Accommodation details delete was failed'], 400);
        }
    }

}