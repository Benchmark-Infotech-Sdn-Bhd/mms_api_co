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
    /**
     * @var vendorServices
     */
    private $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
	 /**
     * @param $request
     * @return true or false
     */

     public function inputValidation($request)
     {
        $input = $request->all();
        $validation = $this->vendor::validate($input);
        return $validation;
     }
	 /**
     * Show the form for creating a new Vendor.
     *
     * @param Request $request
     * @return true
     */
    public function create($request)
    {   
        $vendor = $this->vendor::create([
            'name' => $request["name"],
            'state' => $request["state"],
            'type' => $request["type"],
            'person_in_charge' => $request["person_in_charge"],
            'contact_number' => $request["contact_number"],
            'email_address' => $request["email_address"],
            'address' => $request["address"],
        ]);
        return true;

        
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @return JsonResponse
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
     * @param Request $request, $id
     * @return true or false
     */
    public function updateData($id, $request)
    {             
        try {
            $vendors = $this->vendor::findorfail($id);
            $data = $vendors->update($request->all());
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param $id
     * @return true or false
     */    
    public function delete($id)
    {     
        try {
            // $data->delete();
            $vendors = $this->vendor::find($id);
            $vendors->accommodations()->delete();
            $vendors->insurances()->delete();
            $vendors->transportations()->delete();
            $vendors->delete();
            return true;
    
        } catch (Exception $exception) {
            return false;
        }
    }

}