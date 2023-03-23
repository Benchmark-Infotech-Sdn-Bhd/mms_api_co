<?php

namespace App\Services;

use App\Models\EmbassyAttestationFileCosting;
use App\Services\ValidationServices;

class EmbassyAttestationFileCostingServices
{
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;
    private ValidationServices $validationServices;
    /**
     * EmbassyAttestationFileCostingServices constructor.
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting
     * @param ValidationServices $validationServices
     */
    public function __construct(EmbassyAttestationFileCosting $embassyAttestationFileCosting,ValidationServices $validationServices)
    {
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->embassyAttestationFileCosting->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->embassyAttestationFileCosting->create([
            'country_id' => $request['country_id'] ?? 0,
            'title' => $request['title'] ?? '',
            'amount' => $request['amount'] ?? 0
        ]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->embassyAttestationFileCosting->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $embassyAttestationFileCosting = $this->embassyAttestationFileCosting->find($request['id']);
        if(is_null($embassyAttestationFileCosting)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        return [
            "isUpdated" => $embassyAttestationFileCosting->update([
                'id' => $request['id'],
                'country_id' => $request['country_id'] ?? 0,
                'title' => $request['title'] ?? '',
                'amount' => $request['amount'] ?? 0
            ]),
            "message"=> "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $embassyAttestationFileCosting = $this->embassyAttestationFileCosting->find($request['id']);
        if(is_null($embassyAttestationFileCosting)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $embassyAttestationFileCosting->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function retrieveByCountry($request) : mixed
    {
        if(!($this->validationServices->validate($request,['country_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->embassyAttestationFileCosting->where('country_id',$request['country_id'])->get();
    }
}
