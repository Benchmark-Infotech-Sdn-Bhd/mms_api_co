<?php

namespace App\Exports;

use App\Models\EContractProject;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class ServiceAgreementExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    /**
     * Class constructor.
     *
     * @param int $companyId The ID of the company.
     *
     * @return void
     */
    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Retrieves the query for fetching e-contract projects.
     *
     * @return Builder The query builder object for executing the query.
     */
    public function query()
    {
        return EContractProject::query()
            ->leftJoin('worker_employment', function ($query) {
                $query->on('worker_employment.project_id', '=', 'e-contract_project.id')
                    ->where('worker_employment.service_type', 'e-Contract')
                    ->where('worker_employment.transfer_flag', 0)
                    ->whereNull('worker_employment.remove_date');
            })
            ->leftJoin('workers', function ($query) {
                $query->on('workers.id', '=', 'worker_employment.worker_id')
                    ->whereIN('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'));
            })
            ->leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
            ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
            ->select('crm_prospects.company_name', 'e-contract_project.name as project_name', DB::raw('count(distinct workers.id) as no_of_workers'), 'e-contract_project.valid_until as service_agreement_expiry_date')
            ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->groupBy('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->whereDate('e-contract_project.valid_until', '<', Carbon::now()->addMonths(3))
            ->where('e-contract_applications.company_id', $this->companyId);
    }

    /**
     * Retrieves the headings for the e-contract projects query result.
     *
     * @return array The array of column headings for the query result.
     */
    public function headings(): array
    {
        return ['company_name', 'project_name', 'no_of_workers', 'service_agreement_expiry_date'];
    }
}
