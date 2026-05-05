<?php
namespace App\Http\Controllers;

use App\Models\TicketActivity;
use App\Models\TicketExpense;
use App\Notifications\ExpenseDecisionNotification;
use Illuminate\Http\Request;

class ExpenseApprovalController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canApproveExpenses(), 403);

        $status = $request->input('status', 'pending');

        $base = TicketExpense::query()->where('requested_approver_id', $request->user()->id);

        $expenses = (clone $base)
            ->with(['ticket','addedBy','approvedBy','requestedApprover'])
            ->when(in_array($status, ['pending','approved','rejected']), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = (clone $base)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c','status');

        return view('expenses.approvals', compact('expenses','status','counts'));
    }

    public function approve(Request $request, TicketExpense $expense)
    {
        $this->ensureCanAct($request, $expense);

        $expense->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        TicketActivity::create([
            'ticket_id'   => $expense->ticket_id,
            'user_id'     => $request->user()->id,
            'action_type' => 'expense_added',
            'description' => 'Expense approved: ₹' . number_format($expense->amount, 2),
            'new_value'   => 'approved',
        ]);

        $expense->addedBy?->notify(new ExpenseDecisionNotification($expense));
        return back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, TicketExpense $expense)
    {
        $this->ensureCanAct($request, $expense);

        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);

        $expense->update([
            'status'      => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        TicketActivity::create([
            'ticket_id'   => $expense->ticket_id,
            'user_id'     => $request->user()->id,
            'action_type' => 'expense_added',
            'description' => 'Expense rejected: ' . $data['rejection_reason'],
            'new_value'   => 'rejected',
        ]);

        $expense->addedBy?->notify(new ExpenseDecisionNotification($expense));
        return back()->with('success', 'Expense rejected.');
    }

    /**
     * Approve/reject is allowed for the user the expense was routed to,
     * with admin override.
     */
    private function ensureCanAct(Request $request, TicketExpense $expense): void
    {
        $user = $request->user();
        abort_unless($user->canApproveExpenses(), 403);
        if ($user->isAdmin()) return;
        abort_unless((int) $expense->requested_approver_id === (int) $user->id, 403, 'This expense is not routed to you.');
    }
}
