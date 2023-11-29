<?php

namespace App\Exports;

use App\Models\KinRelationship;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class WorkerImportKinRelationshipSheetExport implements FromQuery, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct()
    {
    }

    public function query()
    {
        return KinRelationship::query()->where('status', 1)
        ->whereNull('deleted_at')
        ->select('id', 'name');
    }
    public function headings(): array
    {
        return ['id', 'name'];
    }
    public function title(): string
    {
        return 'KinRelationship';
    }
}
