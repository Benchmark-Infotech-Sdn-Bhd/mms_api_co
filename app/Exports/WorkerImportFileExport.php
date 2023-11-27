<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class WorkerImportFileExport implements FromCollection,WithHeadings,WithEvents
{
    protected  $users;
    protected  $selects;
    protected  $rowCount;
    protected  $columnCount;
    public function __construct($params)
    {
        $applicationId = $params['application_id'];
        $gender=['Male','Female'];
        $kinRelationship=['Parents','Children','Brothers','Sisters','Grandparents','Uncles','Aunts','Others'];
        $branch=\App\Models\Branch::pluck('branch_name')->toArray();

        $ksmReferenceNumberData =\App\Models\DirectRecruitmentApplicationApproval::
            leftJoin('levy', function($join) use ($applicationId){
                $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
                ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
            })
            ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_application_approval.application_id')
            ->where('directrecruitment_application_approval.application_id', $applicationId)
            ->select('directrecruitment_application_approval.ksm_reference_number')
            ->groupBy('directrecruitment_application_approval.ksm_reference_number')
            ->distinct('directrecruitment_application_approval.ksm_reference_number')
            ->get()->toArray();

        $ksmReferenceNumber= [];
        foreach ($ksmReferenceNumberData as $key => $value) {
            $ksmReferenceNumber[] = $value['ksm_reference_number'];
        }

        $agentData = \App\Models\DirectRecruitmentOnboardingAgent::
            leftJoin('agent', 'directrecruitment_onboarding_agent.agent_id', 'agent.id')
            ->where('directrecruitment_onboarding_agent.application_id', $applicationId)
            ->select('agent.agent_name')
            ->get()->toArray();

        $agent = [];
        foreach ($agentData as $key => $value) {
            $agent[] = $value['agent_name'];
        }

        $selects=[  //selects should have column_name and options
            ['columns_name'=>'C','options'=>$gender],
            ['columns_name'=>'J','options'=>$kinRelationship],
            ['columns_name'=>'L','options'=>$ksmReferenceNumber],
            ['columns_name'=>'AA','options'=>$agent],
        ];
        $this->selects=$selects;
        $this->rowCount=100;//number of rows that will have the dropdown
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
            'kin_relationship',
            'kin_contact_number',
            'ksm_reference_number',
            'bio_medical_refence_number',
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
}
