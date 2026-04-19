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
        $expenses = TicketExpense::with(['ticket','addedBy','approvedBy'])
            ->when(in_array($status, ['pending','approved','rejected']), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(25)->withQueryString();

        $counts = TicketExpense::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c','status');

        return view('expenses.approvals', compact('expenses','status','counts'));
    }

    public function approve(Request $request, TicketExpense $expense)
    {
        abort_unless($request->user()->canApproveExpenses(), 403);
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
        abort_unless($request->user()->canApproveExpenses(), 403);
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
}
