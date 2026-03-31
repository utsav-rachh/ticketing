<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index() { return view('reports.index'); }

    public function priorityReport()
    {
        $data = Ticket::selectRaw('priority, count(*) as total')->groupBy('priority')->get();
        return view('reports.priority', compact('data'));
    }

    public function tatReport()
    {
        $total    = Ticket::count();
        $violated = Ticket::where('is_tat_violated', true)->count();
        $onTime   = $total - $violated;
        $byPriority = Ticket::selectRaw('priority, count(*) as total, sum(is_tat_violated) as violated')
            ->groupBy('priority')->get();
        return view('reports.tat', compact('total','violated','onTime','byPriority'));
    }

    public function expenseReport()
    {
        $data = \App\Models\TicketExpense::selectRaw('date(expense_date) as date, sum(amount) as total')
            ->groupBy('date')->orderByDesc('date')->take(30)->get();
        $grandTotal = \App\Models\TicketExpense::sum('amount');
        return view('reports.expenses', compact('data','grandTotal'));
    }

    public function teamReport()
    {
        $engineers = User::where('role', 'resolver')
            ->withCount(['assignedTickets as total_assigned',
                'assignedTickets as resolved_count' => fn($q) => $q->where('status','resolved'),
                'assignedTickets as violated_count'  => fn($q) => $q->where('is_tat_violated',true),
            ])->get();
        return view('reports.team', compact('engineers'));
    }
}
