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
     * @param $request
     * @return true or false
     */
    public function inputValidation($request)
    {
       $input = $request->all();
       $validation = $this->accommodation::validate($input);
       return $validation;
    }
    /**
     * Show the form for creating a new Accommodation.
     *
     * @param Request $request
     * @return true
     */
    public function create($request)
    {     
        $input = $request->all();
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
        return true;
    }
    /**
     * Display a listing of the Accommodation.
     *
     * @return JsonResponse
     */
    public function show()
    {
        return $this->accommodation::with('vendor')->paginate(10);
    }
    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        return $this->accommodation::findorfail($id);
    }
    /**
     * Update the specified Accommodation data.
     *
     * @param Request $request, $id
     * @return true or false
     */
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
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return true or false
     */    
    public function delete($id)
    {     
        try {
            $data = $this->accommodation::findorfail($id);
            $data->delete();
            return true;
    
        } catch (Exception $exception) {
            return false;
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
        return $this->accommodation::where('accommodation_name', 'like', '%'.$request->name.'%')->get();   
    }

}