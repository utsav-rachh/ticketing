<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    /** @param Collection $users */
    public function __construct(private Collection $users) {}

    public function collection(): Collection { return $this->users; }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Employee ID', 'Phone', 'Role', 'Level', 'Department',
            'Branch', 'State', 'Reports To', 'Auto-route Support Type', 'Auto-route States', 'Status',
        ];
    }

    public function map($user): array
    {
        $autoStates = $user->assignedRegions->isNotEmpty()
            ? $user->assignedRegions->pluck('name')->join(', ')
            : ($user->assignedRegion->name ?? '');

        $status = $user->deleted_at ? 'Deactivated' : ($user->is_active ? 'Active' : 'Inactive');

        return [
            $user->name,
            $user->email,
            $user->employee_id ?? '',
            $user->phone ?? '',
            ucfirst($user->role),
            $user->resolver_level ? strtoupper($user->resolver_level) : '',
            $user->department ?? '',
            $user->branch->name ?? '',
            $user->region->name ?? '',
            $user->supervisor->name ?? '',
            $user->assigned_support_type ?? '',
            $autoStates,
            $status,
        ];
    }
}
