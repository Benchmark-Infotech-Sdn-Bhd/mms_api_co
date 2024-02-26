<?php

namespace App\Exports;

use App\Models\DirectRecruitmentOnboardingAgent;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class WorkerImportAgentSheetExport implements FromQuery, WithHeadings, WithTitle
{
    use Exportable;

    private mixed $param;

    /**
     * Class constructor.
     *
     * @param mixed $param The parameter to initialize the object with.
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Query method to fetch data from the DirectRecruitmentOnboardingAgent model.
     *
     * @return Builder
     */
    public function query()
    {
        return DirectRecruitmentOnboardingAgent::query()
            ->leftJoin('agent', 'directrecruitment_onboarding_agent.agent_id', 'agent.id')
            ->where('directrecruitment_onboarding_agent.application_id', $this->param['application_id'])
            ->select('agent.agent_code', 'agent.agent_name');
    }

    /**
     * Retrieve the headings for the data table.
     *
     * @return array The array containing the column headings.
     */
    public function headings(): array
    {
        return ['code', 'name'];
    }

    /**
     * Returns the title of the method.
     *
     * @return string The title of the method.
     */
    public function title(): string
    {
        return 'Agent';
    }
}
