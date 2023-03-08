<?php

namespace App\Services;

use App\Models\Countries;

class MaintainMastersServices
{
    /**
     * MaintainMastersServices constructor.
     * @param Countries
     */
    public function __construct(Countries $countries)
    {
        $this->countries = $countries;
    }
    /**
     * @param $request
     * @return JsonResponse
     */
    public function create($request)
    {
        if(!($this->countries->validate($request))){
            return $this->countries->errors();
        }
        $country = $this->countries->create([
            'country_name' => $request['country_name'] ?? '',
            'system_type' => $request['system_type'] ?? '',
            'fee' => $request['fee'] ?? 0
        ]);
        return $country;
    }
}
