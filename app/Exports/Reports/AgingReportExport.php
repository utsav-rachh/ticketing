<?php
namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AgingReportExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Collection $tickets) {}

    public function collection(): Collection { return $this->tickets; }

    public function headings(): array
    {
        return [
            'Ticket #', 'Subject', 'Type', 'Priority', 'Status',
            'Branch', 'State', 'Assigned To', 'Aging (days)', 'Aging', 'Created At',
        ];
    }

    public function map($t): array
    {
        return [
            $t->ticket_number,
            $t->subject,
            $t->support_type,
            $t->priority,
            $t->status,
            $t->branch->name ?? '',
            $t->branch->region->name ?? '',
            $t->assignee->name ?? '',
            $t->aging_days,
            $t->aging_human,
            $t->created_at?->format('Y-m-d H:i'),
        ];
    }
}
