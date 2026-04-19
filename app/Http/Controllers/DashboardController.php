<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketExpense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $base = Ticket::visibleTo($user);

        $stats = [
            'total'       => (clone $base)->count(),
            'open'        => (clone $base)->where('status','open')->count(),
            'assigned'    => (clone $base)->where('status','assigned')->count(),
            'in_progress' => (clone $base)->where('status','in_progress')->count(),
            'pending_info'=> (clone $base)->where('status','pending_info')->count(),
            'hold'        => (clone $base)->where('status','hold')->count(),
            'resolved'    => (clone $base)->where('status','resolved')->count(),
            'closed'      => (clone $base)->where('status','closed')->count(),
            'violated'    => (clone $base)->where('is_tat_violated',true)->whereNotIn('status',['resolved','closed'])->count(),
            'red_flag'    => (clone $base)->where('is_red_flag',true)->whereNotIn('status',['resolved','closed'])->count(),
        ];

        // Pending expense approvals — only meaningful for IT Head / admin
        $pendingExpenseCount = $user->canApproveExpenses()
            ? TicketExpense::where('status','pending')->count()
            : null;

        // Tickets grouped by support_type for a small chart (last 30 days)
        $byType = (clone $base)->where('created_at','>=', now()->subDays(30))
            ->selectRaw('support_type, count(*) as total')
            ->groupBy('support_type')->pluck('total','support_type')->all();

        $recentTickets = (clone $base)->with(['creator','category','assignee','branch.region'])
            ->latest()->take(10)->get();

        return view('dashboard', compact('stats','pendingExpenseCount','byType','recentTickets'));
    }
}
