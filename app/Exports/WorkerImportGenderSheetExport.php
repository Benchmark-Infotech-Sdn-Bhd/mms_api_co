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

    /**
     * __construct method initializes the rows property with the values ['Male', 'Female']
     *
     * @return void
     */
    public function __construct()
    {
        $this->rows = ['Male', 'Female'];
    }

    /**
     * map method accepts a parameter $row and returns an array containing $row as the element
     *
     * @param mixed $row The value to be mapped into an array
     *
     * @return array The array containing $row as the element
     */
    public function map($row): array
    {
        return [
            $row
        ];
    }

    /**
     * headings method returns an array with the single value 'name'
     *
     * @return array Returns an array containing the heading value 'name'
     */
    public function headings(): array
    {
        return ['name'];
    }

    /**
     * array method returns the rows property as an array
     *
     * @return array The rows property
     */
    public function array(): array
    {
        return $this->rows;
    }

    /**
     * title method returns the string 'Gender'
     *
     * @return string The string 'Gender'
     */
    public function title(): string
    {
        return 'Gender';
    }
}
