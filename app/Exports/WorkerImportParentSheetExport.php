<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Config;

class WorkerImportParentSheetExport implements FromArray, WithMultipleSheets
{
    private mixed $param;
    private array $sheets;

    /**
     * Constructor method for the class.
     *
     * @param mixed $params The parameters to be assigned to the $param property.
     * @param array $sheets The array of sheets to be assigned to the $sheets property.
     *
     * @return void
     */
    public function __construct($params, array $sheets)
    {
        $this->param = $params;
        $this->sheets = $sheets;
    }

    /**
     * Get the array of sheets.
     *
     * This method returns the array of sheets that are assigned to the $sheets property.
     *
     * @return array The array of sheets.
     */
    public function array(): array
    {
        return $this->sheets;
    }

    /**
     * Returns an array of sheet objects based on the value of $param['template_type'].
     *
     * If $param['template_type'] is equal to the WORKER_BIODATA_TEMPLATE IMPORT_SHEET,
     * this method will return an array containing a single WorkerImportFirstSheetExport object.
     *
     * If $param['template_type'] is equal to the WORKER_BIODATA_TEMPLATE REFERENCE_SHEET,
     * this method will return an array containing the following sheet objects:
     * - WorkerImportGenderSheetExport
     * - WorkerImportKinRelationshipSheetExport
     * - WorkerImportKsmReferenceSheetExport
     * - WorkerImportAgentSheetExport
     *
     * @return array The array of sheet objects.
     */
    public function sheets(): array
    {
        if (isset($this->param['template_type']) && $this->param['template_type'] == Config::get('services.WORKER_BIODATA_TEMPLATE')['import_sheet']) {
            return [
                new WorkerImportFirstSheetExport($this->param)
            ];
        } elseif (isset($this->param['template_type']) && $this->param['template_type'] == Config::get('services.WORKER_BIODATA_TEMPLATE')['reference_sheet']) {
            return [
                new WorkerImportGenderSheetExport(),
                new WorkerImportKinRelationshipSheetExport(),
                new WorkerImportKsmReferenceSheetExport($this->param),
                new WorkerImportAgentSheetExport($this->param),
            ];
        }
        return [];
    }
}
