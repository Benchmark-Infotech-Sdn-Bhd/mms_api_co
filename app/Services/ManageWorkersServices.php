<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkerImportParentSheetExport;

class ManageWorkersServices
{
    private Storage $storage;

    /**
     * ManageWorkersServices constructor.
     * 
     * @param Storage $storage
     * 
     * @return void
     * 
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Export the Template
     * 
     * @param $request The request data containing the application_id, template_type key
     * 
     * @return mixed Returns an export templete file url
     */
    public function exportTemplate($request): mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate(); 
        $fileUrl = '';
        
        if(isset($params['template_type']) && $params['template_type'] == Config::get('services.WORKER_BIODATA_TEMPLATE')['import_sheet']){
            $fileName = "importWorker".$params['application_id'].".xlsx";
            $filePath = '/upload/worker/' . $fileName; 
            Excel::store(new WorkerImportParentSheetExport($params, []), $filePath, 'linode');
            $fileUrl = $this->storage::disk('linode')->url($filePath); 
        }
        
        if(isset($params['template_type']) && $params['template_type'] == Config::get('services.WORKER_BIODATA_TEMPLATE')['reference_sheet']){
            $fileName = "importWorkerReference".$params['application_id'].".xlsx";
            $filePath = '/upload/worker/' . $fileName; 
            Excel::store(new WorkerImportParentSheetExport($params, []), $filePath, 'linode');
            $fileUrl = $this->storage::disk('linode')->url($filePath);
        }           
        return $fileUrl;
    }

}
