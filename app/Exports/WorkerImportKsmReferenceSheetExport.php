<?php

namespace App\Exports;

use App\Models\DirectRecruitmentApplicationApproval;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class WorkerImportKsmReferenceSheetExport implements FromQuery, WithHeadings, WithTitle
{
    use Exportable;

    private mixed $param;

    /**
     * Class constructor.
     *
     * @param mixed $param The value to initialize the $param property with.
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Executes a query to retrieve specific data from the "DirectRecruitmentApplicationApproval" table.
     *
     * @return Builder The query builder instance for further manipulation.
     */
    public function query()
    {
        $applicationId = $this->param['application_id'];

        return DirectRecruitmentApplicationApproval::query()
            ->leftJoin('levy', function ($join) use ($applicationId) {
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

    /**
     * Retrieves the table column headings for the "DirectRecruitmentApplicationApproval" table.
     *
     * @return array An array of column headings.
     */
    public function headings(): array
    {
        return ['ksm_reference_number'];
    }

    /**
     * Retrieves the title of the given method.
     *
     * @return string The title of the method.
     */
    public function title(): string
    {
        return 'KmReferenceNumber';
    }

    /**
     * sets the column datatype.
     *
     * @return array Datatypes of the columns.
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
