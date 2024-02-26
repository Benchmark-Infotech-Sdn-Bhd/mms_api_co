<?php

namespace App\Exports;

use App\Models\DirectRecruitmentApplicationApproval;
use App\Models\DirectRecruitmentOnboardingAgent;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkerImportFileExport implements FromCollection, WithHeadings, WithEvents
{
    private const ROW_COUNT = 100;
    private const COLUMN_COUNT = 5;
    /**
     * @var array|array[]
     */
    private array $selects;
    private int $rowCount;
    private int $columnCount;

    /**
     * Constructor for the class.
     *
     * @param array $params An array of parameters.
     * @return void
     */
    public function __construct(array $params)
    {
        $this->export($params);
    }

    /**
     * Export data based on the given parameters.
     *
     * @param array $params The parameters for exporting data.
     *     - application_id (int): The ID of the application.
     *
     * @return void
     */
    public function export(array $params): void
    {
        $applicationId = $params['application_id'];
        $genderOptions = ['Male', 'Female'];
        $kinRelationshipOptions = ['Parents', 'Children', 'Brothers', 'Sisters', 'Grandparents', 'Uncles', 'Aunts', 'Others'];

        $ksmReferenceNumbers = $this->pluckFromModel(
            DirectRecruitmentApplicationApproval::class,
            'directrecruitment_application_approval.ksm_reference_number',
            'ksm_reference_number',
            $applicationId,
            'levy'
        );
        $agents = $this->pluckFromModel(
            DirectRecruitmentOnboardingAgent::class,
            'agent.agent_name',
            'agent_name',
            $applicationId,
            'agent'
        );

        $columns = [
            ['column_name' => 'C', 'options' => $genderOptions],
            ['column_name' => 'J', 'options' => $kinRelationshipOptions],
            ['column_name' => 'L', 'options' => $ksmReferenceNumbers],
            ['column_name' => 'AA', 'options' => $agents],
        ];

        $this->selects = $columns;
        $this->rowCount = self::ROW_COUNT;
        $this->columnCount = self::COLUMN_COUNT;
    }

    /**
     * Pluck the specified column value from a model based on the given parameters.
     *
     * @param string $model The fully qualified class name of the model to query.
     * @param string $select The column to select from the model's table.
     * @param string $pluck The column to pluck from the selected data.
     * @param string $applicationId The ID of the application to filter the query.
     * @param string $joinModel The fully qualified class name of the model to join with.
     *
     * @return array An array of values from the specified column.
     */
    private function pluckFromModel(string $model, string $select, string $pluck, string $applicationId, string $joinModel): array
    {
        $data = $model::leftJoin($joinModel, function (JoinClause $join) use ($applicationId, $model, $joinModel) {
            $join->on("$joinModel.application_id", '=', "$model.application_id")
                ->on("$joinModel.new_ksm_reference_number", '=', "$model.ksm_reference_number");
        })
            ->where("$model.application_id", $applicationId)
            ->select($select)
            ->distinct($select)
            ->get()
            ->toArray();
        return array_column($data, $pluck);
    }

    /**
     * Create a new collection instance.
     *
     * @return Collection The new collection instance.
     */
    public function collection()
    {
        return collect([]);
    }

    /**
     * Get the column headings for the export.
     *
     * @return array The column headings.
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
            'kin_relationship_id',
            'kin_contact_number',
            'ksm_reference_number',
            'bio_medical_reference_number',
            'bio_medical_valid_until',
            'purchase_date',
            'clinic_name',
            'doctor_code',
            'allocated_xray',
            'xray_code',
            'ig_policy_number',
            'hospitalization_policy_number',
            'insurance_expiry_date',
            'bank_name',
            'account_number',
            'socso_number',
            'agent'
        ];
    }

    /**
     * Register events for the export process.
     *
     * @return array The registered events.
     */
    public function registerEvents(): array
    {
        return [
            // The closure is replaced by a method reference
            AfterSheet::class => [$this, 'handleAfterSheetEvent']
        ];
    }

    /**
     * Handle the AfterSheet event.
     *
     * @param AfterSheet $event The AfterSheet event object.
     *
     * @return void
     * @throws Exception
     */
    private function handleAfterSheetEvent(AfterSheet $event)
    {
        $rowCount = $this->rowCount;

        foreach ($this->selects as $select) {
            $dropColumn = $select['columns_name'];
            $options = $select['options'];
            $validation = $this->setupDropdownValidation($event->sheet, $dropColumn . "2", $options);

            // clone validation to remaining rows
            for ($i = 3; $i <= $rowCount; $i++) {
                $event->sheet->getCell("{$dropColumn}{$i}")->setDataValidation(clone $validation);
            }
            $this->setColumnsAutosize($event->sheet, $this->columnCount);
        }
    }

    /**
     * Setup the dropdown validation for a specific cell in a worksheet.
     *
     * @param $sheet - The worksheet where the validation will be set up.
     * @param string $cell The cell where the validation will be applied.
     * @param array $options The options for the dropdown list.
     *
     * @return DataValidation The configured data validation object.
     */
    private function setupDropdownValidation($sheet, $cell, $options)
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
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

    /**
     * Set the column width to automatically fit the content in the given sheet.
     *
     * @param Worksheet $sheet The sheet to set the column width.
     * @param int $columnCount The number of columns to set the width for.
     *
     * @return void
     */
    private function setColumnsAutosize($sheet, $columnCount)
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $column = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
