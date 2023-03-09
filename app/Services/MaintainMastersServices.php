<?php

namespace App\Services;

use App\Models\Countries;

class MaintainMastersServices
{
    private Countries $countries;
    /**
     * MaintainMastersServices constructor.
     * @param Countries $countries
     */
    public function __construct(Countries $countries)
    {
        $this->countries = $countries;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->countries->validate($request))){
            return $this->countries->errors();
        }
        return $this->countries->create([
            'country_name' => $request['country_name'] ?? '',
            'system_type' => $request['system_type'] ?? '',
            'fee' => $request['fee'] ?? 0
        ]);
    }
}
