<?php


namespace App\Services;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorServices
{
    /**
     * @var vendor
     */
    private Vendor $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
    /**
     * @param $request
     * @return mixed | void
     */
     public function inputValidation($request)
     {
        if(!($this->vendor->validate($request->all()))){
            return $this->vendor->errors();
        }
     }
	 /**
     * Show the form for creating a new Vendor.
     *
     * @param Request $request
     * @return mixed
     */
    public function create($request): mixed
    {  
        $input = $request->all();
        if (request()->hasFile('attachments')){
            $uploadedFile = $request->file('attachments');
            $fileName = time() . '.' . $uploadedFile->getClientOriginalExtension();
            $destinationPath = storage_path('uploads');
            $uploadedFile->move($destinationPath, $fileName);
            $input['attachments'] = "uploads/".$fileName;
        }
        return $this->vendor::create([
            'name' => $input["name"],
            'type' => $input["type"],
            'email_address' => $input["email_address"],
            'contact_number' => $input["contact_number"],            
            'person_in_charge' => $input["person_in_charge"],
            'pic_contact_number' => $input["pic_contact_number"],
            'address' => $input["address"],
            'state' => $input["state"],
            'city' => $input["city"],
            'postcode' => $input["postcode"],
            'attachments' => $input["attachments"],
            'remarks' => $input["remarks"],
        ]);   
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @return LengthAwarePaginator
     */
    public function show()
    {
        return $this->vendor::with('accommodations', 'insurances', 'transportations')->paginate(10);
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {    
        return $this->vendor::find($id);
        // $accommodations = $vendors->accommodations;
        // $insurances = $vendors->insurances;
        // $transportations = $vendors->transportations;
        // $vendors = Vendor::findorfail($id);
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param $id
     * @param $request
     * @return mixed
     */
    public function updateData($id, $request): mixed
    {  
        $input = $request->all();
        $vendors = $this->vendor::findorfail($id);
        if (request()->hasFile('attachments')){
            $uploadedFile = $request->file('attachments');
            $fileName = time() . '.' . $uploadedFile->getClientOriginalExtension();
            $destinationPath = storage_path('uploads');
            $uploadedFile->move($destinationPath, $fileName);
            $input['attachments'] = "uploads/".$fileName;
        }
        return $vendors->update($input);
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {     
        $vendors = $this->vendor::find($id);
        $vendors->accommodations()->delete();
        $vendors->insurances()->delete();
        $vendors->transportations()->delete();
        $vendors->delete();
    }
    /**
     * searching vendor data.
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->vendor->where('name', 'like', '%' . $request->clinic_name . '%')->get(['name',
            'type',
            'email_address',
            'contact_number',
            'person_in_charge',
            'pic_contact_number',
            'address',
            'state',
            'city',
            'postcode',
            'attachments',
            'id']);
    }

}