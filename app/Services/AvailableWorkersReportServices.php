<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use App\Models\Workers;

class AvailableWorkersReportServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * Class constructor.
     *
     * @param Workers $workers An instance of the Workers class.
     * @param ValidationServices $validationServices An instance of the ValidationServices class.
     *
     * This constructor initializes the object with the provided Workers and ValidationServices instances.
     * It assigns the provided Workers instance to the $workers property and the provided ValidationServices
     * instance to the $validationServices property.
     */
    public function __construct(Workers $workers, ValidationServices $validationServices)
    {
        $this->workers = $workers;
        $this->validationServices = $validationServices;
    }

    /**
     * Retrieve a list of workers based on the provided request.
     *
     * @param array $request The request data.
     *        The $request parameter should have the following structure:
     *        [
     *            'search' => (optional) The search keyword. Minimum length of 3 characters is required. (string)
     *            'company_id' => The array of company IDs to filter the workers. (array)
     *            'service_id' => (optional) The ID of the service to filter the workers. (string)
     *            'status' => (optional) The status of the workers to filter. (string)
     *            'export' => (optional) Whether to export the result or not. (string)
     *        ]
     *
     * @return mixed The result of the query.
     *         If the 'search' parameter is provided and fails validation, it will return an array with a 'validate' key
     *         and the validation errors. Otherwise, it will return a paginated list of workers.
     */
    public function list($request): mixed
    {
        if (isset($request['search']) && !empty($request['search'])) {
            if (!($this->validationServices->validate($request, ['search' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        $data = $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
            ->leftjoin('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
            ->leftJoin('worker_employment', function ($query) {
                $query->on('worker_employment.worker_id', '=', 'workers.id')
                    ->whereRaw('worker_employment.id IN (select MAX(WORKER_EMP.id) from worker_employment as WORKER_EMP JOIN workers as WORKER ON WORKER.id = WORKER_EMP.worker_id group by WORKER.id)')
                    ->where('worker_employment.transfer_flag', 0)
                    ->whereNull('worker_employment.remove_date');
            })
            ->leftJoin('total_management_project', 'total_management_project.id', '=', 'worker_employment.project_id')
            ->leftjoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
            ->leftJoin('crm_prospects as total_management_crm', 'total_management_crm.id', '=', 'total_management_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services as total_management_service', 'total_management_service.id', 'total_management_applications.service_id')
            ->leftJoin('e-contract_project as econtract_project', 'econtract_project.id', '=', 'worker_employment.project_id')
            ->leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'econtract_project.application_id')
            ->leftJoin('crm_prospects as econtract_crm', 'econtract_crm.id', '=', 'e-contract_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services as econtract_service', 'econtract_service.id', 'e-contract_applications.service_id')
            ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
            ->leftjoin('directrecruitment_applications', 'directrecruitment_applications.id', '=', 'directrecruitment_workers.application_id')
            ->leftJoin('crm_prospects as directrecruitment_crm', 'directrecruitment_crm.id', '=', 'directrecruitment_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services as directrecruitment_service', 'directrecruitment_service.id', 'directrecruitment_applications.service_id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if (isset($request['service_id']) && !empty($request['service_id'])) {
                    $query->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.service_id
                WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.service_id
                WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.service_id
                ELSE '' END) = '" . $request['service_id'] . "'");
                }
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%')
                        ->orWhereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_crm.company_name
                WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_crm.company_name
                WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_crm.company_name
                ELSE '" . Config::get('services.FOMNEXTS_DETAILS')['company_name'] . "' END) like '%" . $request['search'] . "%'");
                }
                if (isset($request['status']) && !empty($request['status'])) {
                    $query->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
                WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
                WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN 'On-Bench'
                ELSE 'On-Bench' END) = '" . $request['status'] . "'");
                }

            })
            ->whereNotNull('workers.crm_prospect_id');
        if (isset($request['export']) && !empty($request['export'])) {
            $data = $data->select('workers.name', 'workers.passport_number', 'workers.gender', 'worker_visa.ksm_reference_number')
                ->selectRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_crm.company_name
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_crm.company_name
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_crm.company_name
        ELSE '" . Config::get('services.FOMNEXTS_DETAILS')['company_name'] . "' END) as company_name,   (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.sector_name
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.sector_name
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.sector_name
        ELSE '" . Config::get('services.FOMNEXTS_DETAILS')['sector'] . "' END) as sector_name, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.service_name
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.service_name
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.service_name
        ELSE '' END) as service_type, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
        WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN 'On-Bench'
        ELSE 'On-Bench' END) as status")
                ->distinct('workers.id')
                ->orderBy('workers.id', 'DESC')
                ->get();
        } else {
            $data = $data->select('workers.id', 'workers.name', 'workers.passport_number', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.total_management_status', 'workers.module_type', 'worker_employment.id as worker_employment_id')
                ->selectRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_crm.company_name
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_crm.company_name
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_crm.company_name
        ELSE '" . Config::get('services.FOMNEXTS_DETAILS')['company_name'] . "' END) as company_name,   (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.sector_name
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.sector_name
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.sector_name
        ELSE '" . Config::get('services.FOMNEXTS_DETAILS')['sector'] . "' END) as sector_name, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.service_name
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.service_name
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.service_name
        ELSE '' END) as service_type, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
        WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN 'On-Bench'
        ELSE 'On-Bench' END) as status")
                ->distinct('workers.id')
                ->orderBy('workers.id', 'DESC')
                ->paginate(Config::get('services.paginate_row'));
        }
        return $data;
    }

}
