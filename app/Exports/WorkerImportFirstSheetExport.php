<?php

namespace App\Exports;

use Illuminate\Support\Collection;
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
     * Returns an empty collection.
     *
     * @return Collection
     */
    public function collection()
    {
        return collect([]);
    }

    /**
     * Returns an array of headings for a data table.
     *
     * @return array An array containing the headings for the data table.
     */
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
            'kin_relationship',
            'kin_contact_number',
            'ksm_reference_number',
            'bio_medical_reference_number',
            'bio_medical_valid_until',
            'agent_code'
        ];
    }

    /**
     * Registers the events for the AfterSheet class.
     *
     * @return array The registered events.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $rowCount = $this->rowCount;
                $columnCount = $this->columnCount;
                foreach ($this->selects as $select){
                    $dropColumn = $select['columns_name'];
                    $options = $select['options'];
                    $validation = $this->setUpDataValidation($event, $dropColumn, $options);

                    $this->cloneValidationToRemainingRows($validation, $event, $dropColumn, $rowCount);

                    $this->setAutoSizeToColumns($event, $columnCount);
                }
            },
        ];
    }

// This method sets up a data validation

    /**
     * Set up data validation for a given cell.
     *
     * @param object $event The AfterSheet event object.
     * @param string $dropColumn The column for which data validation needs to be set up.
     * @param array $options The array of options for the data validation.
     */
    private function setUpDataValidation($event, $dropColumn, $options)
    {
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
        $validation->setFormula1(sprintf('"%s"', implode(',', $options)));

        return $validation;
    }

// This method clones the validation to the remaining rows

    /**
     * Clones a data validation object and applies it to the remaining rows in a spreadsheet.
     *
     * @param DataValidation $validation The data validation object to be cloned and applied.
     * @param $event - The event object containing the spreadsheet.
     * @param string $dropColumn The column letter indicating where the data validation should be applied.
     * @param int $rowCount The total number of rows in the spreadsheet.
     *
     * @return void
     */
    private function cloneValidationToRemainingRows($validation, $event, $dropColumn, $rowCount)
    {
        for ($i = 3; $i <= $rowCount; $i++) {
            $event->sheet->getCell("{$dropColumn}{$i}")->setDataValidation(clone $validation);
        }
    }

// This method sets columns to autosize

    /**
     * Sets the auto size property to columns in the given event's sheet.
     *
     * @param $event - The event object containing the sheet to apply auto-size to columns.
     * @param $columnCount - The number of columns to apply auto-size to.
     */
    private function setAutoSizeToColumns($event, $columnCount)
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $column = Coordinate::stringFromColumnIndex($i);
            $event->sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Returns the title of the worker details.
     *
     * @return string - The title of the worker details.
     */
    public function title(): string
    {
        return 'WorkerDetails';
    }
}
