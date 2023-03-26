<?php

namespace App\Services;

use App\Models\Sectors;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;

class SectorsServices
{
    private Sectors $sectors;
    private ValidationServices $validationServices;
    /**
     * SectorsServices constructor.
     * @param Sectors $sectors
     * @param ValidationServices $validationServices
     */
    public function __construct(Sectors $sectors,ValidationServices $validationServices)
    {
        $this->sectors = $sectors;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->sectors->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->sectors->create([
            'sector_name' => $request['sector_name'] ?? '',
            'sub_sector_name' => $request['sub_sector_name'] ?? '',
            'checklist_status' => $request['checklist_status']
        ]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->sectors->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $sector = $this->sectors->find($request['id']);
        if(is_null($sector)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        return [
            "isUpdated" => $sector->update([
                'id' => $request['id'],
                'sector_name' => $request['sector_name'] ?? '',
                'sub_sector_name' => $request['sub_sector_name'] ?? '',
                'checklist_status' => $request['checklist_status']
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
        $sector = $this->sectors->find($request['id']);
        if(is_null($sector)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $sector->delete(),
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
        return $this->sectors->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function retrieveAll() : mixed
    {
        return $this->sectors->orderBy('sectors.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return array
     */
    public function updateChecklistStatus($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required','checklist_status' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $sector = $this->sectors->find($request['id']);
        if(is_null($sector)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $sector->checklist_status = $request['checklist_status'];
        return  [
            "isUpdated" => $sector->save() == 1 ? true:false,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function searchSectors($request) : mixed
    {
        if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->sectors->where('sector_name', 'like', "%{$request['search_param']}%")->orderBy('sectors.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
