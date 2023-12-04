<?php

namespace App\Exports;

use App\Models\DirectRecruitmentApplicationApproval;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class WorkerImportKsmReferenceSheetExport implements FromQuery, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function query()
    {
        $applicationId = $this->param['application_id'];
        
        return DirectRecruitmentApplicationApproval::query()
            ->leftJoin('levy', function($join) use ($applicationId){
                $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
                ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
            })
            ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_application_approval.application_id')
            ->where('directrecruitment_application_approval.application_id', $applicationId)
            ->select('directrecruitment_application_approval.ksm_reference_number')
            ->distinct('directrecruitment_application_approval.ksm_reference_number')
            ->orderBy('directrecruitment_application_approval.ksm_reference_number')
            ->groupBy('directrecruitment_application_approval.ksm_reference_number');
    }
    public function headings(): array
    {
        return ['ksm_reference_number'];
    }
    public function title(): string
    {
        return 'KmReferenceNumber';
    }
}
