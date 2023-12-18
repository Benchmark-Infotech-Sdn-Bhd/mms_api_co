<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Config;

class WorkerImportParentSheetExport implements FromArray, WithMultipleSheets
{
    protected $sheets;

    public function __construct($params, array $sheets)
    {
        $this->param = $params;
        $this->sheets = $sheets;
    }

    public function array(): array
    {
        return $this->sheets;
    }

    public function sheets(): array
    {
        if(isset($this->param['template_type']) && $this->param['template_type'] == Config::get('services.WORKER_BIODATA_TEMPLATE')[0]){
            return [
                new WorkerImportFirstSheetExport($this->param) 
            ];
        }elseif(isset($this->param['template_type']) && $this->param['template_type'] == Config::get('services.WORKER_BIODATA_TEMPLATE')[1]){
            return [
                new WorkerImportGenderSheetExport(),
                new WorkerImportKinRelationshipSheetExport(),       
                new WorkerImportKsmReferenceSheetExport($this->param), 
                new WorkerImportAgentSheetExport($this->param), 
            ];
        }
    }
}