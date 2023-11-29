<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class WorkerImportFirstSheetExport implements FromCollection,WithHeadings,WithEvents,WithTitle
{
    protected  $users;
    protected  $selects;
    protected  $rowCount;
    protected  $columnCount;
    public function __construct($params)
    {
        $selects=[];
        $this->selects=$selects;
        $this->rowCount=1;//number of rows that will have the dropdown
        $this->columnCount=5;//number of columns to be auto sized
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([]);
    }
    public function headings(): array
    {
        return [
            'name',
            'date_of_birth',
            'gender',
            'passport_number',
            'passport_valid_until',
            'address',
            'city',
            'state',
            'kin_name',
            'kin_relationship_id',
            'kin_contact_number',
            'ksm_reference_number',
            'bio_medical_reference_number',
            'bio_medical_valid_until',
            'agent_id'
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // handle by a closure.
            AfterSheet::class => function(AfterSheet $event) {
                $rowCount = $this->rowCount;
                $columnCount = $this->columnCount;
                foreach ($this->selects as $select){
                    $dropColumn = $select['columns_name'];
                    $options = $select['options'];
                    // set dropdown list for first data row
                    $validation = $event->sheet->getCell("{$dropColumn}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST );
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION );
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input error');
                    $validation->setError('Value is not in list.');
                    $validation->setPromptTitle('Pick from list');
                    $validation->setPrompt('Please pick a value from the drop-down list.');
                    $validation->setFormula1(sprintf('"%s"',implode(',',$options)));

                    // clone validation to remaining rows
                    for ($i = 3; $i <= $rowCount; $i++) {
                        $event->sheet->getCell("{$dropColumn}{$i}")->setDataValidation(clone $validation);
                    }
                    // set columns to autosize
                    for ($i = 1; $i <= $columnCount; $i++) {
                        $column = Coordinate::stringFromColumnIndex($i);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }

            },
        ];
    }

    public function title(): string
    {
        return 'WorkerDetails';
    }
}
