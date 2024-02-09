<?php

namespace App\Services;

use App\Models\EmbassyAttestationFileCosting;
use App\Models\Countries;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\CountriesServices;

class EmbassyAttestationFileCostingServices
{
    /**
     * @var EmbassyAttestationFileCosting
     */
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;

    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * @var CountriesServices
     */
    private CountriesServices $countriesServices;

    /**
     * @var Countries
     */
    private Countries $countries;

    /**
     * Constructor method.
     * 
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting Instance of the EmbassyAttestationFileCosting class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * @param CountriesServices $countriesServices Instance of the CountriesServices class.
     * @param Countries $countries Instance of the Countries class.
     */
    public function __construct(
        EmbassyAttestationFileCosting     $embassyAttestationFileCosting,
        ValidationServices                $validationServices,
        CountriesServices                 $countriesServices,
        Countries                         $countries
    )
    {
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->validationServices = $validationServices;
        $this->countriesServices = $countriesServices;
        $this->countries = $countries;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if (!($this->validationServices->validate($request,$this->embassyAttestationFileCosting->rules))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $countryDetails = $this->countries->where('company_id', $request['company_id'])->find($request['country_id']);
        if (is_null($countryDetails)) {
            return [
                'InvalidUser' => true
            ];
        }
        $filecosting = $this->embassyAttestationFileCosting->create([
            'country_id' => (int)$request['country_id'] ?? 0,
            'title' => $request['title'] ?? '',
            'amount' => (float)$request['amount'] ?? 0,
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
        ]);
        $count = $this->embassyAttestationFileCosting->whereNull('deleted_at')
        ->where('country_id','=',$request['country_id'])->count('id');
        if ($count == 1) {
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
        if (!($this->validationServices->validate($request,$this->embassyAttestationFileCosting->rulesForUpdation))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $embassyAttestationFileCosting = $this->embassyAttestationFileCosting->find($request['id']);
        if (is_null($embassyAttestationFileCosting)) {
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $countryDetails = $this->countries->where('company_id', $request['company_id'])->find($embassyAttestationFileCosting->country_id);
        if (is_null($countryDetails)) {
            return [
                'InvalidUser' => true
            ];
        }
        return [
            "isUpdated" => $embassyAttestationFileCosting->update([
                'id' => $request['id'],
                'country_id' => (int)$request['country_id'] ?? $embassyAttestationFileCosting['country_id'],
                'title' => $request['title'] ?? $embassyAttestationFileCosting['title'],
                'amount' => (float)$request['amount'] ?? $embassyAttestationFileCosting['amount'],
                'modified_by'   => $request['modified_by'] ?? $embassyAttestationFileCosting['modified_by']
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
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $embassyAttestationFileCosting = $this->embassyAttestationFileCosting->find($request['id']);
        if (is_null($embassyAttestationFileCosting)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $countryDetails = $this->countries->where('company_id', $request['company_id'])->find($embassyAttestationFileCosting->country_id);
        if (is_null($countryDetails)) {
            return [
                'InvalidUser' => true
            ];
        }
        $res = [
            "isDeleted" => $embassyAttestationFileCosting->delete(),
            "message" => "Deleted Successfully"
        ];
        if ($res['isDeleted']) {
            $count = $this->embassyAttestationFileCosting->whereNull('deleted_at')
            ->where('country_id','=',$embassyAttestationFileCosting['country_id'])->count('id');
            if ($count == 0) {
            $result =  $this->countriesServices->updateCostingStatus([ 'id' => $embassyAttestationFileCosting['country_id'], 'costing_status' => 'Pending' ]);
            }
        }
        return $res;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $embassyAttestationDetails = $this->embassyAttestationFileCosting->find($request['id']);
        if (is_null($embassyAttestationDetails)) {
            return [
                "error" => true,
                "message" => "Data not found"
            ];
        }
        $countryDetails = $this->countries->where('company_id', $request['company_id'])->find($embassyAttestationDetails->country_id);
        if (is_null($countryDetails)) {
            return [
                'InvalidUser' => true
            ];
        }
        return $embassyAttestationDetails;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if (!($this->validationServices->validate($request,['country_id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $countryDetails = $this->countries->where('company_id', $request['company_id'])->find($request['country_id']);
        if (is_null($countryDetails)) {
            return [
                'InvalidUser' => true
            ];
        }
        return $this->embassyAttestationFileCosting->where('country_id',$request['country_id'])
        ->select('id','title','amount')
        ->orderBy('embassy_attestation_file_costing.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
