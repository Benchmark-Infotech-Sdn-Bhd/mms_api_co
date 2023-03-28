<?php

namespace App\Services;

use App\Models\EmbassyAttestationFileCosting;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\CountriesServices;

class EmbassyAttestationFileCostingServices
{
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;
    private ValidationServices $validationServices;
    private CountriesServices $countriesServices;
    /**
     * EmbassyAttestationFileCostingServices constructor.
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting
     * @param ValidationServices $validationServices
     * @param CountriesServices $countriesServices
     */
    public function __construct(EmbassyAttestationFileCosting $embassyAttestationFileCosting,ValidationServices $validationServices,CountriesServices $countriesServices)
    {
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->validationServices = $validationServices;
        $this->countriesServices = $countriesServices;
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
        $filecosting = $this->embassyAttestationFileCosting->create([
            'country_id' => $request['country_id'] ?? 0,
            'title' => $request['title'] ?? '',
            'amount' => $request['amount'] ?? 0
        ]);
        $count = $this->embassyAttestationFileCosting->whereNull('deleted_at')->count('id');
        if($count == 1){
          $result =  $this->countriesServices->updateCostingStatus([ 'id' => $request['country_id'], 'costing_status' => 'Done' ]);
        }
        return $filecosting;
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
        $res = [
            "isDeleted" => $embassyAttestationFileCosting->delete(),
            "message" => "Deleted Successfully"
        ];
        if($res['isDeleted']){
            $count = $this->embassyAttestationFileCosting->whereNull('deleted_at')->count('id');
            if($count == 0){
            $result =  $this->countriesServices->updateCostingStatus([ 'id' => $embassyAttestationFileCosting['country_id'], 'costing_status' => 'Pending' ]);
            }
        }
        return $res;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->embassyAttestationFileCosting->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function retrieveAll() : mixed
    {
        return $this->embassyAttestationFileCosting->orderBy('embassy_attestation_file_costing.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
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
        return $this->embassyAttestationFileCosting->where('country_id',$request['country_id'])->orderBy('embassy_attestation_file_costing.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
