<?php
namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class TatComplianceExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private int $total,
        private int $onTime,
        private int $violated,
        private Collection $byPriority,
    ) {}

    public function sheets(): array
    {
        return [
            new TatSummarySheet($this->total, $this->onTime, $this->violated),
            new TatByPrioritySheet($this->byPriority),
        ];
    }
}

class TatSummarySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private int $total, private int $onTime, private int $violated) {}

    public function array(): array
    {
        $compliance = $this->total > 0 ? round($this->onTime / $this->total * 100, 1) : 100;
        return [[$this->total, $this->onTime, $this->violated, $compliance . '%']];
    }

    public function headings(): array
    {
        return ['Total Tickets', 'On Time', 'TAT Violated', 'Compliance %'];
    }

    public function title(): string { return 'Summary'; }
}

class TatByPrioritySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Collection $byPriority) {}

    public function array(): array
    {
        return $this->byPriority->map(function ($row) {
            $compliance = $row->total > 0 ? round((1 - $row->violated / $row->total) * 100, 1) : 100;
            return [ucfirst($row->priority), $row->total, $row->violated, $compliance . '%'];
        })->all();
    }

    public function headings(): array
    {
        return ['Priority', 'Total', 'Violated', 'Compliance %'];
    }

    public function title(): string { return 'By Priority'; }
}
