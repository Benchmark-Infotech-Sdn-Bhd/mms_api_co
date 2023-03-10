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
     * @return JsonResponse
     */
    public function inputValidation($request)
    {
        if(!($this->accommodation->validate($request->all()))){
            return $this->accommodation->errors();
        }
    }
    /**
     * Show the form for creating a new Accommodation.
     *
     * @param Request $request
     * @return mixed
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
        return $this->accommodation::create([
            'name' => $input["name"],
            'location' => $input["location"],
            'square_feet' => $input["square_feet"],
            'accommodation_name' => $input["accommodation_name"],
            'maximum_pax_per_room' => $input["maximum_pax_per_room"],
            'cost_per_pax' => $input["cost_per_pax"],
            'attachment' => $input["attachment"],
            'deposit' => $input["rent_deposit"],
            'rent_per_month' => $input["rent_per_month"],
            'vendor_id' => $input["vendor_id"],
        ]);
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
     * @return mixed
     */
    public function update($id, $request)
    {    
        $data = $this->accommodation::findorfail($id);
        $input = $request->all();
        if (request()->hasFile('attachment')){
            $uploadedImage = $request->file('attachment');
            $imageName = time() . '.' . $uploadedImage->getClientOriginalExtension();
            $destinationPath = storage_path('images');
            $uploadedImage->move($destinationPath, $imageName);
            $input['attachment'] = "images/".$imageName;
        }
        return $data->update($input);
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return mixed
     */    
    public function delete($id)
    {   
        $data = $this->accommodation::findorfail($id);
        $data->delete();
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