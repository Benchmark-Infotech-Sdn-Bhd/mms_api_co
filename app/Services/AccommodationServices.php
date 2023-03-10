<?php


namespace App\Services;

use App\Models\Accommodation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccommodationServices
{
    /**
     * @var accommodation
     */
    private Accommodation   $accommodation;

    public function __construct(Accommodation $accommodation)
    {
        $this->accommodation = $accommodation;
    }
    /**
     * @param $request
     * @return mixed | void
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
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {     
        $input = $request->all();
        if (request()->hasFile('attachment')){
            $uploadedfile = $request->file('attachment');
            $fileName = time() . '.' . $uploadedfile->getClientOriginalExtension();
            $destinationPath = storage_path('uploads');
            $uploadedfile->move($destinationPath, $fileName);
            $input['attachment'] = "uploads/".$fileName;
        }
        return $this->accommodation::create([
            'name' => $input["name"],
            'location' => $input["location"],
            'square_feet' => $input["square_feet"],
            'accommodation_name' => $input["accommodation_name"],
            'maximum_pax_per_room' => $input["maximum_pax_per_room"],
            'cost_per_pax' => $input["cost_per_pax"],
            'attachment' => $input["attachment"],
            'deposit' => $input["deposit"],
            'rent_per_month' => $input["rent_per_month"],
            'vendor_id' => $input["vendor_id"],
        ]);
    }
    /**
     * Display a listing of the Accommodation.
     *
     * @return LengthAwarePaginator
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
     * @param $id
     * @param $request
     * @return mixed
     */
    public function update($id, $request): mixed
    {    
        $data = $this->accommodation::findorfail($id);
        $input = $request->all();
        if (request()->hasFile('attachment')){
            $uploadedfile = $request->file('attachment');
            $fileName = time() . '.' . $uploadedfile->getClientOriginalExtension();
            $destinationPath = storage_path('uploads');
            $uploadedfile->move($destinationPath, $fileName);
            $input['attachment'] = "uploads/".$fileName;
        }
        return $data->update($input);
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {   
        $data = $this->accommodation::findorfail($id);
        $data->delete();
    }
    /**
     * searching Accommodation data.
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->accommodation->where('accommodation_name', 'like', '%' . $request->name . '%')->get(['name',
            'location',
            'square_feet',
            'accommodation_name',
            'maximum_pax_per_room',
            'cost_per_pax',
            'attachment',
            'deposit',
            'rent_per_month',
            'vendor_id',
            'id']);
    }

}