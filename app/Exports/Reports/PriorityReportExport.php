<?php
namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PriorityReportExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Collection $rows, private int $total) {}

    public function collection(): Collection { return $this->rows; }

    public function headings(): array
    {
        return ['Priority', 'Total', 'Share %'];
    }

    public function map($row): array
    {
        return [
            ucfirst($row->priority),
            $row->total,
            $this->total > 0 ? round($row->total / $this->total * 100, 1) : 0,
        ];
    }
}
