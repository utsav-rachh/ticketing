<?php
namespace App\Http\Controllers;

use App\Exports\Reports\AgingReportExport;
use App\Exports\Reports\ExpenseReportExport;
use App\Exports\Reports\PriorityReportExport;
use App\Exports\Reports\TatComplianceExport;
use App\Exports\Reports\TeamPerformanceExport;
use App\Models\Region;
use App\Models\Ticket;
use App\Models\TicketExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function priorityReport(Request $request)
    {
        $q = Ticket::query();
        $this->applyRegionFilter($q, $request);
        $data  = $q->selectRaw('priority, count(*) as total')->groupBy('priority')->get();
        $total = (int) $data->sum('total');

        if ($this->wantsExcel($request)) {
            return Excel::download(
                new PriorityReportExport($data, $total),
                'priority-report-' . now()->format('Ymd-His') . '.xlsx'
            );
        }

        $regions = Region::active()->orderBy('name')->get();
        return view('reports.priority', compact('data','regions','total'));
    }

    public function tatReport(Request $request)
    {
        $q = Ticket::query();
        $this->applyRegionFilter($q, $request);
        $total    = (clone $q)->count();
        $violated = (clone $q)->where('is_tat_violated', true)->count();
        $onTime   = max(0, $total - $violated);
        $byPriority = (clone $q)->selectRaw('priority, count(*) as total, sum(is_tat_violated) as violated')
            ->groupBy('priority')->get();

        if ($this->wantsExcel($request)) {
            return Excel::download(
                new TatComplianceExport($total, $onTime, $violated, $byPriority),
                'tat-compliance-' . now()->format('Ymd-His') . '.xlsx'
            );
        }

        $regions = Region::active()->orderBy('name')->get();
        return view('reports.tat', compact('total','violated','onTime','byPriority','regions'));
    }

    public function expenseReport(Request $request)
    {
        $q = TicketExpense::query();
        if ($request->filled('region_id')) {
            $q->whereHas('ticket.branch', fn ($b) => $b->where('region_id', $request->input('region_id')));
        }
        $data = (clone $q)->selectRaw('date(expense_date) as date, sum(amount) as total')
            ->groupBy('date')->orderByDesc('date')->take(30)->get();
        $byStatus = (clone $q)->selectRaw('status, count(*) as cnt, sum(amount) as total')
            ->groupBy('status')->get()->keyBy('status');
        $approvedTotal = (float) ($byStatus['approved']->total ?? 0);
        $pendingTotal  = (float) ($byStatus['pending']->total ?? 0);
        $rejectedTotal = (float) ($byStatus['rejected']->total ?? 0);

        if ($this->wantsExcel($request)) {
            return Excel::download(
                new ExpenseReportExport($data, $byStatus, $approvedTotal, $pendingTotal, $rejectedTotal),
                'expense-report-' . now()->format('Ymd-His') . '.xlsx'
            );
        }

        $regions = Region::active()->orderBy('name')->get();
        return view('reports.expenses', compact('data','byStatus','approvedTotal','pendingTotal','rejectedTotal','regions'));
    }

    public function teamReport(Request $request)
    {
        $engineers = User::where('role', 'resolver')
            ->with('assignedRegion')
            ->withCount([
                'assignedTickets as total_assigned',
                'assignedTickets as resolved_count' => fn ($q) => $q->where('status','resolved'),
                'assignedTickets as violated_count' => fn ($q) => $q->where('is_tat_violated',true),
                'assignedTickets as aging_1d'   => fn ($q) => $q->whereNotIn('status',['resolved','closed'])->where('created_at','>', now()->subDay()),
                'assignedTickets as aging_1_3d' => fn ($q) => $q->whereNotIn('status',['resolved','closed'])
                    ->whereBetween('created_at', [now()->subDays(3), now()->subDay()]),
                'assignedTickets as aging_3_7d' => fn ($q) => $q->whereNotIn('status',['resolved','closed'])
                    ->whereBetween('created_at', [now()->subDays(7), now()->subDays(3)]),
                'assignedTickets as aging_7d'   => fn ($q) => $q->whereNotIn('status',['resolved','closed'])
                    ->where('created_at','<', now()->subDays(7)),
            ])->orderBy('resolver_level')->orderBy('name')->get();

        if ($this->wantsExcel($request)) {
            return Excel::download(
                new TeamPerformanceExport($engineers),
                'team-performance-' . now()->format('Ymd-His') . '.xlsx'
            );
        }

        return view('reports.team', compact('engineers'));
    }

    /**
     * Aging report — open tickets bucketed by how long they've been open.
     * Useful for management to see what's been sitting around.
     */
    public function agingReport(Request $request)
    {
        $q = Ticket::query()->with(['branch.region','assignee'])
            ->whereNotIn('status', ['resolved','closed']);
        $this->applyRegionFilter($q, $request);

        $tickets = (clone $q)->orderBy('created_at')->get();

        $buckets = [
            'lt_1d'  => 0,
            'd1_3'   => 0,
            'd3_7'   => 0,
            'gt_7d'  => 0,
        ];
        foreach ($tickets as $t) {
            $days = (int) optional($t->created_at)->diffInDays(now());
            if      ($days < 1) $buckets['lt_1d']++;
            elseif  ($days < 3) $buckets['d1_3']++;
            elseif  ($days < 7) $buckets['d3_7']++;
            else                $buckets['gt_7d']++;
        }

        if ($this->wantsExcel($request)) {
            return Excel::download(
                new AgingReportExport($tickets),
                'aging-report-' . now()->format('Ymd-His') . '.xlsx'
            );
        }

        $regions = Region::active()->orderBy('name')->get();
        return view('reports.aging', compact('tickets','buckets','regions'));
    }

    private function applyRegionFilter($q, Request $request): void
    {
        if ($request->filled('region_id')) {
            $q->whereHas('branch', fn ($b) => $b->where('region_id', $request->input('region_id')));
        }
    }

    private function wantsExcel(Request $request): bool
    {
        return strtolower($request->input('format', '')) === 'xlsx';
    }
}
