<?php
namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExpenseReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private Collection $daily,
        private Collection $byStatus,
        private float $approvedTotal,
        private float $pendingTotal,
        private float $rejectedTotal,
    ) {}

    public function sheets(): array
    {
        return [
            new ExpenseStatusSheet($this->byStatus, $this->approvedTotal, $this->pendingTotal, $this->rejectedTotal),
            new ExpenseDailySheet($this->daily),
        ];
    }
}

class ExpenseStatusSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private Collection $byStatus,
        private float $approved,
        private float $pending,
        private float $rejected,
    ) {}

    public function array(): array
    {
        $rows = [];
        foreach (['approved' => $this->approved, 'pending' => $this->pending, 'rejected' => $this->rejected] as $status => $total) {
            $count = $this->byStatus[$status]->cnt ?? 0;
            $rows[] = [ucfirst($status), $count, number_format($total, 2, '.', '')];
        }
        return $rows;
    }

    public function headings(): array
    {
        return ['Status', 'Count', 'Total Amount (INR)'];
    }

    public function title(): string { return 'By Status'; }
}

class ExpenseDailySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Collection $daily) {}

    public function array(): array
    {
        return $this->daily->map(function ($row) {
            return [\Carbon\Carbon::parse($row->date)->format('Y-m-d'), number_format($row->total, 2, '.', '')];
        })->all();
    }

    public function headings(): array
    {
        return ['Date', 'Daily Total (INR)'];
    }

    public function title(): string { return 'Daily Totals'; }
}
