<?php

namespace App\Exports;

use App\Models\BulkUploadRecords;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class FailureExport implements FromQuery
{
    use Exportable;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function query()
    {
        return BulkUploadRecords::query()->where('success_flag', 0)
        ->where('bulk_upload_id', $this->id)
        ->select('parameter', 'comments', 'status', 'success_flag', 'created_at');
    }
}
