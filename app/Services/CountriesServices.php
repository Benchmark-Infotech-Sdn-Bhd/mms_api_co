<?php

namespace App\Services;

use App\Models\Countries;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;

class CountriesServices
{
    private Countries $countries;
    private ValidationServices $validationServices;
    /**
     * CountriesServices constructor.
     * @param Countries $countries
     * @param ValidationServices $validationServices
     */
    public function __construct(Countries $countries,ValidationServices $validationServices)
    {
        $this->countries = $countries;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->countries->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        return $this->countries->create([
            'country_name' => $request['country_name'] ?? '',
            'system_type' => $request['system_type'] ?? '',
            'costing_status' => "Pending",
            'fee' => $request['fee'] ?? 0,
            'bond' => $request['bond'] ?? 0
        ]);
    }
    /**
     * @param $request
     * @return array
     */
    public function update($request) : array
    {
        if(!($this->validationServices->validate($request,$this->countries->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $country = $this->countries->find($request['id']);
        if(is_null($country)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        return  [
            "isUpdated" => $country->update([
                'id' => $request['id'],
                'country_name' => $request['country_name'] ?? $country['country_name'],
                'system_type' => $request['system_type'] ?? $country['system_type'],
                'costing_status' => $country['costing_status'],
                'fee' => $request['fee'] ?? $country['fee'],
                'bond' => $request['bond'] ?? $country['bond']
            ]),
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return array
     */
    public function delete($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $country = $this->countries->find($request['id']);
        if(is_null($country)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $country->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->countries->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function dropdown() : mixed
    {
        return $this->countries->select('id','country_name')->orderBy('countries.created_at','DESC')->get();
    }
    /**
     * @param $request
     * @return array
     */
    public function updateCostingStatus($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required','costing_status' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $country = $this->countries->find($request['id']);
        if(is_null($country)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $country->costing_status = $request['costing_status'];
        return  [
            "isUpdated" => $country->save() == 1,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|regex:/^[a-zA-Z ]*$/|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->countries->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('country_name', 'like', "%{$request['search_param']}%");
            }
        })->select('id','country_name','system_type','costing_status')
        ->orderBy('countries.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
