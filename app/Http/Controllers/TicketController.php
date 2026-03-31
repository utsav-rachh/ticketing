<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAttachment;
use App\Models\TicketExpense;
use App\Models\TatConfiguration;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $tickets = Ticket::visibleTo($user)->with(['creator','category','subcategory','assignee'])
            ->latest()->paginate(20);
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('support_type')->orderBy('sort_order')->get();
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'support_type'   => 'required|in:application,infrastructure,admin',
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'subject'        => 'required|string|max:500',
            'description'    => 'nullable|string',
            'priority'       => 'required|in:critical,high,medium,low',
        ]);

        $tat     = TatConfiguration::forPriority($data['priority']);
        $tatHours= $tat ? $tat->tat_hours : 8;

        $ticket = Ticket::create([
            ...$data,
            'created_by'   => auth()->id(),
            'status'       => 'open',
            'tat_hours'    => $tatHours,
            'tat_deadline' => now()->addHours($tatHours),
            'ticket_number'=> Ticket::generateTicketNumber(),
        ]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'action_type' => 'created',
            'description' => 'Ticket created by ' . auth()->user()->name,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created: ' . $ticket->ticket_number);
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load(['creator','category','subcategory','assignee','activities.user','expenses.addedBy','attachments.uploader']);

        $assignableUsers = [];
        if (auth()->user()->canAssign()) {
            $assignableUsers = $this->getAssignableUsers($ticket);
        }

        return view('tickets.show', compact('ticket','assignableUsers'));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $data = $request->validate(['status' => 'required|in:open,assigned,in_progress,pending_info,resolved,closed']);

        $old = $ticket->status;
        $updates = ['status' => $data['status']];
        if ($data['status'] === 'resolved') $updates['resolved_at'] = now();
        if ($data['status'] === 'closed')   $updates['closed_at']   = now();

        $ticket->update($updates);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'action_type' => 'status_changed',
            'description' => 'Status changed from ' . $old . ' to ' . $data['status'],
            'old_value'   => $old,
            'new_value'   => $data['status'],
        ]);

        $ticket->creator->notify(new TicketStatusNotification($ticket, $old, $data['status']));

        return back()->with('success', 'Status updated.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);
        $data = $request->validate(['assigned_to' => 'required|exists:users,id']);

        $ticket->update([
            'assigned_to' => $data['assigned_to'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'status'      => 'assigned',
        ]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'action_type' => 'assigned',
            'description' => 'Ticket assigned to ' . $ticket->assignee->name,
            'new_value'   => $ticket->assignee->name,
        ]);

        $ticket->assignee->notify(new TicketAssignedNotification($ticket));

        return back()->with('success', 'Ticket assigned.');
    }

    public function addActivity(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $data = $request->validate(['description' => 'required|string']);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'action_type' => 'note_added',
            'description' => $data['description'],
        ]);

        return back()->with('success', 'Note added.');
    }

    public function addExpense(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $data = $request->validate([
            'description'  => 'required|string|max:500',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        $expense = TicketExpense::create([...$data, 'ticket_id' => $ticket->id, 'added_by' => auth()->id()]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'action_type' => 'expense_added',
            'description' => 'Expense added: ₹' . number_format($data['amount'], 2),
        ]);

        return back()->with('success', 'Expense added.');
    }

    public function addAttachment(Request $request, Ticket $ticket)
    {
        $request->validate(['attachment' => 'required|file|max:10240']);
        $file = $request->file('attachment');
        $path = $file->store('attachments/' . $ticket->id, 'public');

        TicketAttachment::create([
            'ticket_id'   => $ticket->id,
            'uploaded_by' => auth()->id(),
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_size'   => $file->getSize(),
            'mime_type'   => $file->getMimeType(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function export(Request $request, string $format)
    {
        abort(404, 'Export not implemented yet');
    }

    private function getAssignableUsers(Ticket $ticket): array
    {
        return \App\Models\User::where('role', 'resolver')
            ->where('is_active', true)
            ->get(['id', 'name', 'role'])
            ->toArray();
    }
}
