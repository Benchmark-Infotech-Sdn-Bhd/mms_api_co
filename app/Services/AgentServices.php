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
            'company_address' => $request['company_address'] ?? '',
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
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
                'agent_name' => $request['agent_name'] ?? $agent['agent_name'],
                'country_id' => $request['country_id'] ?? $agent['country_id'],
                'city' => $request['city'] ?? $agent['city'],
                'person_in_charge' => $request['person_in_charge'] ?? $agent['person_in_charge'],
                'pic_contact_number' => $request['pic_contact_number'] ?? $agent['pic_contact_number'],
                'email_address' => $request['email_address'] ?? $agent['email_address'],
                'company_address' => $request['company_address'] ?? $agent['company_address'],
                'modified_by'   => $request['modified_by'] ?? $agent['modified_by']
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
    public function show($request) : mixed
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
        return $this->agent->join('countries', 'countries.id', '=', 'agent.country_id')
        ->select('agent.id','agent.agent_name','countries.country_name','agent.city','agent.person_in_charge')
        ->orderBy('agent.created_at','DESC')
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
        return $this->agent->join('countries', 'countries.id', '=', 'agent.country_id')
        ->where('country_id',$request['country_id'])->select('agent.id','agent.agent_name','countries.country_name','agent.city','agent.person_in_charge')
        ->orderBy('agent.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->agent->join('countries', 'countries.id', '=', 'agent.country_id')
        ->where(function($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('agent_name', 'like', '%'.$request['search_param'].'%')
                    ->orWhere('countries.country_name', 'like', '%'.$request['search_param'].'%')
                    ->orWhere('city', 'like', '%'.$request['search_param'].'%')
                    ->orWhere('person_in_charge', 'like', '%'.$request['search_param'].'%');
            }
        })->select('agent.id','agent.agent_name','countries.country_name','agent.city','agent.person_in_charge')
        ->orderBy('agent.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
