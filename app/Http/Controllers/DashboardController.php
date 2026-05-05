<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketExpense;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $base = Ticket::visibleTo($user);

        $stats = [
            'total'    => (clone $base)->count(),
            'open'     => (clone $base)->whereIn('status', ['open','assigned','in_progress','pending_info'])->count(),
            'hold'     => (clone $base)->where('status','hold')->count(),
            'resolved' => (clone $base)->whereIn('status',['resolved','closed'])->count(),
            'violated' => (clone $base)->where('is_tat_violated',true)->whereNotIn('status',['resolved','closed'])->count(),
            'red_flag' => (clone $base)->where('is_red_flag',true)->whereNotIn('status',['resolved','closed'])->count(),
        ];

        // Pending expense approvals — only show what's routed to this user.
        $pendingExpenseCount = $user->canApproveExpenses()
            ? TicketExpense::where('status','pending')
                ->where('requested_approver_id', $user->id)
                ->count()
            : null;

        // Management tickets: red-flagged or raised by management — top of dashboard.
        $managementTickets = (clone $base)
            ->where(function ($q) {
                $q->where('is_red_flag', true)
                  ->orWhereHas('creator', fn ($c) => $c->where('role', 'management'));
            })
            ->whereNotIn('status', ['closed'])
            ->with(['creator','category','subcategory','assignee','branch.region'])
            ->latest()
            ->take(10)
            ->get();

        // Recent tickets: exclude any IDs already shown in the management block,
        // so a ticket never appears twice on the dashboard.
        $managementIds = $managementTickets->pluck('id')->all();
        $recentTickets = (clone $base)
            ->when(!empty($managementIds), fn ($q) => $q->whereNotIn('id', $managementIds))
            ->with(['creator','category','subcategory','assignee','branch.region'])
            ->latest()->take(10)->get();

        $canQuickAssign = $user->isAdmin() || $user->isITHead() || $user->isTL();
        $assignableUsers = collect();
        if ($canQuickAssign) {
            $q = User::where('role', 'resolver')->where('is_active', true);
            if ($user->isTL()) {
                $q->where('assigned_support_type', $user->assigned_support_type);
            }
            $assignableUsers = $q->orderBy('name')->get(['id','name','resolver_level','assigned_support_type']);
        }

        return view('dashboard', compact('stats','pendingExpenseCount','managementTickets','recentTickets','canQuickAssign','assignableUsers'));
    }
}
