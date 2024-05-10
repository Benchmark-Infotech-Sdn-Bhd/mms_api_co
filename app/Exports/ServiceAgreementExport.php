<?php

namespace App\Exports;

use App\Models\EContractProject;
use App\Models\TotalManagementProject;
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

    private string $notificationType;

    private int $days;

    private string $modules;

    /**
     * Class constructor.
     *
     * @param int $companyId The ID of the company.
     *
     * @return void
     */
    public function __construct(int $companyId, string $notificationType, int $days, string $modules)
    {
        $this->companyId = $companyId;
        $this->notificationType = $notificationType;
        $this->days = $days;
        $this->modules = $modules;
    }

    /**
     * Retrieves the query for fetching e-contract projects.
     *
     * @return Builder The query builder object for executing the query.
     */
    public function query()
    {
        if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], explode(",",$this->modules))) {
                $eContractServiceAgreement = EContractProject::leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
                        ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
                        ->select('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
                        ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
                        ->whereDate('e-contract_project.valid_until', '<', Carbon::now()->addDays($this->days))
                        ->whereDate('e-contract_project.valid_until', '>=', Carbon::now())
                        ->where('e-contract_applications.company_id', $this->companyId)
                        ->get()->toArray();
            }
            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], explode(",",$this->modules))) {
                $totalManagementserviceAgreement = TotalManagementProject::leftjoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
                                ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'total_management_applications.crm_prospect_id')
                                ->select('total_management_project.id', 'total_management_project.name', 'total_management_project.valid_until', 'crm_prospects.company_name')
                                ->distinct('total_management_project.id', 'total_management_project.name', 'total_management_project.valid_until', 'crm_prospects.company_name')
                                ->whereDate('total_management_project.valid_until', '<', Carbon::now()->addDays($this->days))
                                ->whereDate('total_management_project.valid_until', '>=', Carbon::now())
                                ->where('total_management_applications.company_id', $this->companyId)
                                ->get()->toArray();
            }
            return array_unique(array_merge($eContractServiceAgreement, $totalManagementserviceAgreement), SORT_REGULAR);

        } else if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], explode(",",$this->modules))) {
                $eContractServiceAgreement = EContractProject::leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
                        ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
                        ->select('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
                        ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
                        ->whereDate('e-contract_project.valid_until', '>=', Carbon::now()->subDays($this->days))
                        ->whereDate('e-contract_project.valid_until', '<', Carbon::now())
                        ->where('e-contract_applications.company_id', $this->companyId)
                        ->get()->toArray();
            }
            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], explode(",",$this->modules))) {
                $totalManagementserviceAgreement = TotalManagementProject::leftjoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
                        ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'total_management_applications.crm_prospect_id')
                        ->select('total_management_project.id', 'total_management_project.name', 'total_management_project.valid_until', 'crm_prospects.company_name')
                        ->distinct('total_management_project.id', 'total_management_project.name', 'total_management_project.valid_until', 'crm_prospects.company_name')
                        ->whereDate('total_management_project.valid_until', '>=', Carbon::now()->subDays($this->days))
                        ->whereDate('total_management_project.valid_until', '<', Carbon::now())
                        ->where('total_management_applications.company_id', $this->companyId)
                        ->get()->toArray();
            }
            return array_unique(array_merge($eContractServiceAgreement, $totalManagementserviceAgreement), SORT_REGULAR);
        }
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
