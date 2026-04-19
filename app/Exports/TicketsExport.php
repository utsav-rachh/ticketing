<?php
namespace App\Exports;

use App\Models\Ticket;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private User $user,
        private array $filters = [],
    ) {}

    public function query()
    {
        $q = Ticket::visibleTo($this->user)
            ->with(['creator','assignee','category','subcategory','branch.region','vendor','expenses']);

        foreach (['status','support_type','priority','is_red_flag'] as $f) {
            if (!empty($this->filters[$f])) $q->where($f, $this->filters[$f]);
        }
        if (!empty($this->filters['region_id'])) {
            $q->whereHas('branch', fn ($b) => $b->where('region_id', $this->filters['region_id']));
        }
        if (!empty($this->filters['from'])) $q->whereDate('created_at', '>=', $this->filters['from']);
        if (!empty($this->filters['to']))   $q->whereDate('created_at', '<=', $this->filters['to']);

        return $q->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Ticket #','Support Type','Category','Subcategory','Custom Issue',
            'Subject','Description','Priority','Status','Red Flag','TAT Violated',
            'Branch','Region',
            'Raised By','Employee ID','Contact Name','Contact Phone','Contact Email',
            'Assignee','Assignee Level','Assigned At',
            'Hold Hours','TAT Hours','TAT Deadline',
            'Created At','Resolved At','Closed At',
            'Vendor','Expenses Approved (₹)','Expenses Pending (₹)',
            'Last Update Note',
        ];
    }

    public function map($t): array
    {
        $approvedSum = $t->expenses->where('status','approved')->sum('amount');
        $pendingSum  = $t->expenses->where('status','pending')->sum('amount');
        $lastUpdate  = $t->updates()->latest('created_at')->first()?->note;

        return [
            $t->ticket_number,
            $t->support_type,
            $t->category->name ?? '',
            $t->subcategory->name ?? '',
            $t->custom_issue,
            $t->subject,
            $t->description,
            $t->priority,
            $t->status,
            $t->is_red_flag ? 'Yes' : '',
            $t->is_tat_violated ? 'Yes' : '',
            $t->branch->name ?? '',
            $t->branch->region->name ?? '',
            $t->creator->name ?? '',
            $t->creator->employee_id ?? '',
            $t->employee_contact_name,
            $t->employee_contact_phone,
            $t->employee_contact_email,
            $t->assignee->name ?? '',
            $t->assignee->resolver_level ?? '',
            $t->assigned_at?->format('Y-m-d H:i'),
            round(($t->hold_total_seconds ?? 0) / 3600, 2),
            $t->tat_hours,
            $t->tat_deadline?->format('Y-m-d H:i'),
            $t->created_at?->format('Y-m-d H:i'),
            $t->resolved_at?->format('Y-m-d H:i'),
            $t->closed_at?->format('Y-m-d H:i'),
            $t->vendor->name ?? '',
            number_format($approvedSum, 2, '.', ''),
            number_format($pendingSum, 2, '.', ''),
            $lastUpdate,
        ];
    }
}
