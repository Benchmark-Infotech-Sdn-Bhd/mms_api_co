<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
        return [
            new WorkerImportFirstSheetExport($this->param),
            new WorkerImportGenderSheetExport(),
            new WorkerImportKinRelationshipSheetExport(),       
            new WorkerImportKsmReferenceSheetExport($this->param), 
            new WorkerImportAgentSheetExport($this->param), 
        ];

    }
}