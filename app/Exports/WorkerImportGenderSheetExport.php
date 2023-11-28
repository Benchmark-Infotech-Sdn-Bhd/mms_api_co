<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkerImportGenderSheetExport implements FromArray, WithHeadings, WithTitle, WithMapping
{
    use Exportable;

    public function __construct()
    {
        $this->rows = ['Male','Female'];
    }

    public function map($row): array
    {
        return [
            $row
        ];
    }

    public function headings(): array
    {
        return ['name'];
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Gender';
    }
}
