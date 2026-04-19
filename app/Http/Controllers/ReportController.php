<?php
namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Ticket;
use App\Models\TicketExpense;
use App\Models\User;
use Illuminate\Http\Request;

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
        $data = $q->selectRaw('priority, count(*) as total')->groupBy('priority')->get();
        $regions = Region::active()->orderBy('name')->get();
        return view('reports.priority', compact('data','regions'));
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
        $approvedTotal = $byStatus['approved']->total ?? 0;
        $pendingTotal  = $byStatus['pending']->total ?? 0;
        $rejectedTotal = $byStatus['rejected']->total ?? 0;
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
        return view('reports.team', compact('engineers'));
    }

    private function applyRegionFilter($q, Request $request): void
    {
        if ($request->filled('region_id')) {
            $q->whereHas('branch', fn ($b) => $b->where('region_id', $request->input('region_id')));
        }
    }
}
