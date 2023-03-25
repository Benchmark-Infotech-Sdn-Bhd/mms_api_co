<?php

namespace App\Services;

use App\Models\Agent;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;

class AgentServices
{
    private Agent $agent;
    private ValidationServices $validationServices;
    /**
     * AgentServices constructor.
     * @param Agent $agent
     * @param ValidationServices $validationServices
     */
    public function __construct(Agent $agent,ValidationServices $validationServices)
    {
        $this->agent = $agent;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->agent->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->agent->create([
            'agent_name' => $request['agent_name'] ?? '',
            'country_id' => $request['country_id'],
            'city' => $request['city'] ?? '',
            'person_in_charge' => $request['person_in_charge'] ?? '',
            'pic_contact_number' => $request['pic_contact_number'] ?? '',
            'email_address' => $request['email_address'] ?? '',
            'company_address' => $request['company_address'] ?? ''
        ]);
    }
    /**
     * @param $request
     * @return array
     */
    public function update($request) : array
    {
        if(!($this->validationServices->validate($request,$this->agent->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $agent = $this->agent->find($request['id']);
        if(is_null($agent)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        return  [
            "isUpdated" => $agent->update([
                'id' => $request['id'],
                'agent_name' => $request['agent_name'] ?? '',
                'country_id' => $request['country_id'],
                'city' => $request['city'] ?? '',
                'person_in_charge' => $request['person_in_charge'] ?? '',
                'pic_contact_number' => $request['pic_contact_number'] ?? '',
                'email_address' => $request['email_address'] ?? '',
                'company_address' => $request['company_address'] ?? ''
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
        $agent = $this->agent->find($request['id']);
        if(is_null($agent)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $agent->delete(),
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
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->agent->with('countries')->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function retrieveAll() : mixed
    {
        return $this->agent->with('countries')->orderBy('agent.created_at','DESC')
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
        return $this->agent->with('countries')->where('country_id',$request['country_id'])->orderBy('agent.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function searchAgents($request) : mixed
    {
        if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->agent->with('countries')->join('countries', 'countries.id', '=', 'agent.country_id')
        ->where(function($query) use ($request) {
            $query->where('agent_name', 'LIKE', '%'.$request['search_param'].'%')
                ->orWhere('countries.country_name', 'LIKE', '%'.$request['search_param'].'%')
                ->orWhere('city', 'LIKE', '%'.$request['search_param'].'%')
                ->orWhere('person_in_charge', 'LIKE', '%'.$request['search_param'].'%');
        })->orderBy('agent.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
