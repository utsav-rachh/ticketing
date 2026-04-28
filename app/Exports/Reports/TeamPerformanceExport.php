<?php
namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeamPerformanceExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Collection $engineers) {}

    public function collection(): Collection { return $this->engineers; }

    public function headings(): array
    {
        return [
            'Resolver', 'Level', 'Support Type', 'State',
            'Assigned', 'Resolved', 'Violated', 'Resolution %',
            '< 1d', '1-3d', '3-7d', '> 7d',
        ];
    }

    public function map($eng): array
    {
        $resolutionPct = $eng->total_assigned > 0
            ? round($eng->resolved_count / $eng->total_assigned * 100, 1)
            : 0;
        return [
            $eng->name,
            strtoupper($eng->resolver_level ?? '—'),
            $eng->assigned_support_type ?? '—',
            $eng->assignedRegion->name ?? '—',
            $eng->total_assigned,
            $eng->resolved_count,
            $eng->violated_count,
            $resolutionPct . '%',
            $eng->aging_1d,
            $eng->aging_1_3d,
            $eng->aging_3_7d,
            $eng->aging_7d,
        ];
    }
}
