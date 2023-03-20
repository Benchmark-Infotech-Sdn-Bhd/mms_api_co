<?php

namespace App\Services;

use App\Models\Countries;
use App\Services\ValidationServices;

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
            return $this->validationServices->errors();
        }
        return $this->countries->create([
            'country_name' => $request['country_name'] ?? '',
            'system_type' => $request['system_type'] ?? '',
            'fee' => $request['fee'] ?? 0
        ]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->countries->rulesForUpdation))){
            return $this->validationServices->errors();
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
                'country_name' => $request['country_name'] ?? '',
                'system_type' => $request['system_type'] ?? '',
                'fee' => $request['fee'] ?? 0
            ]),
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return $this->validationServices->errors();
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
    public function retrieve($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return $this->validationServices->errors();
        }
        return $this->countries->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function retrieveAll() : mixed
    {
        return $this->countries->get();
    }
}
