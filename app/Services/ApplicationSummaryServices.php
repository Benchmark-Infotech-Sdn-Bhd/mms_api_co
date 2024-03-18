<?php

namespace App\Services;

use App\Models\ApplicationSummary;
use App\Models\DirectrecruitmentApplications;
use App\Models\FWCMS;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class ApplicationSummaryServices
{
    /**
     * @var ApplicationSummary
     */
    private ApplicationSummary $applicationSummary;


    /**
     * @var FWCMS
     */
    private FWCMS $fwcms;

    /**
     * Class constructor.
     *
     * @param ApplicationSummary $applicationSummary An instance of ApplicationSummary class.
     * @param FWCMS $fwcms An instance of FWCMS class.
     */
    public function __construct(ApplicationSummary $applicationSummary, FWCMS $fwcms)
    {
        $this->applicationSummary = $applicationSummary;
        $this->fwcms = $fwcms;
    }

    /**
     * Get the list of application summaries based on the provided request parameters.
     *
     * @param array $request The request parameters containing 'company_id', 'application_id', and 'ksm_reference_number'.
     * @return mixed Returns the paginated list of application summaries.
     */
    public function list($request): mixed
    {
        return $this->applicationSummary
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'application_summary.application_id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('application_summary.application_id', $request['application_id'])
            ->where(function ($query) use ($request) {
                if (!empty($request['ksm_reference_number'])) {
                    $query->where('application_summary.ksm_reference_number', $request['ksm_reference_number']);
                    $query->orWhere('application_summary.ksm_reference_number', null);
                }
            })
            ->select('application_summary.id', 'application_summary.application_id', 'application_summary.action', 'application_summary.status', 'application_summary.created_at', 'application_summary.updated_at', 'application_summary.ksm_reference_number')
            ->orderBy('application_summary.id', 'asc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Update the status of an application summary.
     *
     * @param array $request The request data
     *                      - application_id (optional | integer): The ID of the application
     *                      - action (optional | string): The action
     *                      - status (optional | string): The status
     *                      - created_by (optional | integer): The ID of the user who created the summary
     *
     * @return bool|array Returns true if the status is updated successfully, otherwise returns false if the application summary is not found
     */
    public function updateStatus($request): bool|array
    {
        $applicationSummary = $this->applicationSummary->where([
            ['application_id', $request['application_id']],
            ['action', $request['action']],
            ['ksm_reference_number', null]
        ])->first(['id', 'application_id', 'action', 'status', 'created_by', 'modified_by', 'created_at', 'updated_at', 'ksm_reference_number']);

        if (is_null($applicationSummary)) {
            $this->applicationSummary->create([
                'application_id' => $request['application_id'] ?? 0,
                'action' => $request['action'] ?? '',
                'status' => $request['status'] ?? '',
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            $applicationSummary->update([
                'status' => $request['status'] ?? '',
                'modified_by' => $request['created_by'] ?? 0,
                'updated_at' => Carbon::now()
            ]);
        }

        return true;
    }

    /**
     * Delete the status from the application summary.
     *
     * @param array $request The request parameters containing the application ID and action.
     * @return bool Returns true if the status is successfully deleted, otherwise false.
     */
    public function deleteStatus($request): mixed
    {
        $applicationSummary = $this->applicationSummary->where([
            ['application_id', $request['application_id']],
            ['action', $request['action']]
        ])->first();
        if (is_null($applicationSummary)) {
            return false;
        } else {
            $applicationSummary->delete();
        }
        return true;
    }

    /**
     * Update status of an application in application summary.
     *
     * @param array $request The data for updating the application status.
     *     - application_id (integer) The ID of the application.
     *     - action (string) The action of the application.
     *     - status (string) The new status of the application.
     *     - created_by (integer) The ID of the user who created the application.
     *     - ksm_reference_number (string) The KSM reference number of the application.
     *
     * @return bool|array Returns true if the application status was successfully updated, false otherwise.
     */
    public function ksmUpdateStatus($request): bool|array
    {
        $applicationSummary = $this->applicationSummary->where([
            ['application_id', $request['application_id']],
            ['action', $request['action']],
            ['ksm_reference_number', $request['ksm_reference_number']]
        ])->first(['id', 'application_id', 'action', 'status', 'created_by', 'modified_by', 'created_at', 'updated_at', 'ksm_reference_number']);

        if (is_null($applicationSummary)) {
            $this->applicationSummary->create([
                'application_id' => $request['application_id'] ?? 0,
                'action' => $request['action'] ?? '',
                'status' => $request['status'] ?? '',
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'ksm_reference_number' => $request['ksm_reference_number'] ?? ''
            ]);
        } else {
            $applicationSummary->update([
                'status' => $request['status'] ?? '',
                'modified_by' => $request['created_by'] ?? 0,
                'updated_at' => Carbon::now()
            ]);
        }
        return true;
    }

    /**
     * Retrieves a list of KSM reference numbers based on the given request parameters.
     *
     * @param mixed $request The request parameters to filter the KSM reference numbers.
     * @return mixed The list of KSM reference numbers that match the request parameters.
     */
    public function listKsmReferenceNumber($request): mixed
    {
        return $this->fwcms
            ->leftJoin('application_interviews', function ($join) {
                $join->on('application_interviews.ksm_reference_number', '=', 'fwcms.ksm_reference_number')
                    ->where('application_interviews.status', '=', 'Approved');
            })
            ->leftJoin('levy', 'levy.ksm_reference_number', 'application_interviews.ksm_reference_number')
            ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.ksm_reference_number', 'levy.new_ksm_reference_number')
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'fwcms.application_id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where([
                ['fwcms.application_id', $request['application_id']],
                ['fwcms.status', 'Approved']
            ])->select('fwcms.id', 'fwcms.ksm_reference_number', 'application_interviews.approval_date', 'levy.new_ksm_reference_number', 'levy.approved_quota', 'directrecruitment_application_approval.valid_until')
            ->distinct('fwcms.id', 'fwcms.ksm_reference_number')
            ->orderBy('fwcms.id', 'DESC')->get();
    }
}
