<?php

namespace App\Exports;

use App\Models\DirectRecruitmentOnboardingAgent;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class WorkerImportAgentSheetExport implements FromQuery, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function query()
    {
        $applicationId = $this->param['application_id'];

        return DirectRecruitmentOnboardingAgent::query()
            ->leftJoin('agent', 'directrecruitment_onboarding_agent.agent_id', 'agent.id')
            ->where('directrecruitment_onboarding_agent.application_id', $applicationId)
            ->select('directrecruitment_onboarding_agent.id', 'agent.agent_name');
    }
    public function headings(): array
    {
        return ['id', 'name'];
    }
    public function title(): string
    {
        return 'Agent';
    }
}
