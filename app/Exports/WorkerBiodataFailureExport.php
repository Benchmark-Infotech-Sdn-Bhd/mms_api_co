<?php

namespace App\Exports;

use App\Models\BulkUploadRecords;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class WorkerBiodataFailureExport implements FromQuery, WithHeadings
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
        ->select(DB::raw('json_unquote(JSON_EXTRACT(parameter, "$.name")) as name, json_unquote(JSON_EXTRACT(parameter, "$.date_of_birth")) as date_of_birth, json_unquote(JSON_EXTRACT(parameter, "$.gender")) as gender, json_unquote(JSON_EXTRACT(parameter, "$.passport_number")) as passport_number, json_unquote(JSON_EXTRACT(parameter, "$.passport_valid_until")) as passport_valid_until, json_unquote(JSON_EXTRACT(parameter, "$.address")) as address, json_unquote(JSON_EXTRACT(parameter, "$.city")) as city, json_unquote(JSON_EXTRACT(parameter, "$.state")) as state, json_unquote(JSON_EXTRACT(parameter, "$.kin_name")) as kin_name, json_unquote(JSON_EXTRACT(parameter, "$.kin_relationship")) as kin_relationship, json_unquote(JSON_EXTRACT(parameter, "$.kin_contact_number")) as kin_contact_number, json_unquote(JSON_EXTRACT(parameter, "$.ksm_reference_number")) as ksm_reference_number, json_unquote(JSON_EXTRACT(parameter, "$.bio_medical_reference_number")) as bio_medical_reference_number, json_unquote(JSON_EXTRACT(parameter, "$.bio_medical_valid_until")) as bio_medical_valid_until'), 'comments');
    }
    public function headings(): array
    {
        return ['name', 'date_of_birth', 'gender', 'passport_number', 'passport_valid_until', 'address', 'city', 'state', 'kin_name', 'kin_relationship', 'kin_contact_number', 'ksm_reference_number', 'bio_medical_reference_number', 'bio_medical_valid_until', 'comments'];
    }
}
