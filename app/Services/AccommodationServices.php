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
    /**
     * @var accommodation
     */
    private $accommodation;

    public function __construct(Accommodation $accommodation)
    {
        $this->accommodation = $accommodation;
    }
    /**
     * Show the form for creating a new Accommodation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create($request)
    {     
        $input = $request->all();
        $validation = $this->accommodation::validate($input);
        if ($validation !== true) {
            return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
        }

        if (request()->hasFile('attachment')){
            $uploadedImage = $request->file('attachment');
            $imageName = time() . '.' . $uploadedImage->getClientOriginalExtension();
            $destinationPath = storage_path('images');
            $uploadedImage->move($destinationPath, $imageName);
            $input['attachment'] = "images/".$imageName;
        }
        $accommodationData = $this->accommodation::create([
            'accommodation_name' => $input["accommodation_name"],
            'number_of_units' => $input["number_of_units"],
            'number_of_rooms' => $input["number_of_rooms"],
            'maximum_pax_per_room' => $input["maximum_pax_per_room"],
            'cost_per_pax' => $input["cost_per_pax"],
            'attachment' => $input["attachment"],
            'rent_deposit' => $input["rent_deposit"],
            'rent_per_month' => $input["rent_per_month"],
            'rent_advance' => $input["rent_advance"],
            'vendor_id' => $input["vendor_id"],
        ]);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$accommodationData],200);
    }
    /**
     * Display a listing of the Accommodation.
     *
     * @return JsonResponse
     */
    public function show()
    {
        $accommodationData = $this->accommodation::with('vendor')->paginate(10);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$accommodationData],200);
    }
    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $accommodationData = $this->accommodation::findorfail($id);
        return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$accommodationData],200);
    }
    /**
     * Update the specified Accommodation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function update($id, $request)
    {     
        try {
            $data = $this->accommodation::findorfail($id);
            $input = $request->all();
            $validation = $this->accommodation::validate($input);
            if ($validation !== true) {
                return response()->json(['error'=>'true','statusCode'=>422,'statusMessage'=>'Unprocessable Entity Error','data'=>$validation],422);
            }
            if (request()->hasFile('attachment')){
                $uploadedImage = $request->file('attachment');
                $imageName = time() . '.' . $uploadedImage->getClientOriginalExtension();
                $destinationPath = storage_path('images');
                $uploadedImage->move($destinationPath, $imageName);
                $input['attachment'] = "images/".$imageName;
            }
            $accommodationData = $data->update($input);
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>$input],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return JsonResponse
     */    
    public function delete($id)
    {     
        try {
            $data = $this->accommodation::findorfail($id);
            $data->delete();
            return response()->json(['error'=>'false','statusCode'=>200,'statusMessage'=>'Ok','data'=>''],200);
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }
    /**
     * searching Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search($request)
    {     
        try {
            $accommodation = $this->accommodation::where('accommodation_name', 'like', '%'.$request->name.'%')->get();   
            return $accommodation;
    
        } catch (Exception $exception) {
            return response()->json(['error'=>'false','statusCode'=>400,'statusMessage'=>'Bad Request','data'=>'"message": "You are not authorized to access the api."'],400);
        }
    }

}