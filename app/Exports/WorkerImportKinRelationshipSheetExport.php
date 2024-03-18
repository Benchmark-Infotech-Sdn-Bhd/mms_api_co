<?php

namespace App\Exports;

use App\Models\KinRelationship;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class WorkerImportKinRelationshipSheetExport implements FromQuery, WithHeadings, WithTitle
{
    use Exportable;

    /**
     * Returns a query builder instance for the KinRelationship model.
     *
     * This method allows you to retrieve a query builder instance for the KinRelationship model
     * with specific conditions applied. It applies the following conditions to the query builder:
     * - where the 'status' column is equal to 1
     * - where the 'deleted_at' column is null
     * It also selects only the 'id' and 'name' columns from the KinRelationship model.
     *
     * @return Builder The query builder instance for the KinRelationship model.
     */
    public function query()
    {
        return KinRelationship::query()->where('status', 1)
            ->whereNull('deleted_at')
            ->select('id', 'name');
    }

    /**
     * Returns an array of column headings.
     *
     * This method returns an array containing the column headings for a specific entity.
     * The headings are specified in the order they should appear.
     *
     * @return array An array of column headings.
     */
    public function headings(): array
    {
        return ['id', 'name'];
    }

    /**
     * Returns the title of the KinRelationship model.
     *
     * This method returns the title of the KinRelationship model as a string.
     *
     * @return string The title of the KinRelationship model.
     */
    public function title(): string
    {
        return 'KinRelationship';
    }
}
