<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAttachment;
use App\Models\TicketExpense;
use App\Models\TicketUpdate;
use App\Models\TatConfiguration;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\ExpenseSubmittedNotification;
use App\Notifications\ManagementTicketNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketStatusNotification;
use App\Services\TatCalculator;
use App\Services\TicketAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    private const STATUSES = ['open','assigned','in_progress','pending_info','hold','resolved','closed'];

    public function index(Request $request)
    {
        $user    = $request->user();
        $q       = Ticket::visibleTo($user)->with(['creator','category','subcategory','assignee','branch.region']);

        foreach (['status','support_type','priority','is_red_flag'] as $filter) {
            if ($request->filled($filter)) {
                $q->where($filter, $request->input($filter));
            }
        }
        if ($request->filled('region_id')) {
            $q->whereHas('branch', fn ($b) => $b->where('region_id', $request->input('region_id')));
        }
        if ($request->filled('from')) $q->whereDate('created_at', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->whereDate('created_at', '<=', $request->input('to'));

        $tickets = $q->latest()->paginate(20)->withQueryString();
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('support_type')->orderBy('sort_order')->get();
        $vendors    = Vendor::active()->orderBy('name')->get();
        $branches   = Branch::active()->with('region')->orderBy('name')->get();
        return view('tickets.create', compact('categories','vendors','branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'support_type'   => 'required|in:application,infrastructure,admin',
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'vendor_id'      => 'nullable|exists:vendors,id',
            'subject'        => 'required|string|max:500',
            'description'    => 'nullable|string',
            'custom_issue'   => 'nullable|string|max:500',
            'employee_contact_name'  => 'nullable|string|max:150',
            'employee_contact_phone' => 'nullable|string|max:20',
            'employee_contact_email' => 'nullable|email|max:150',
            'attachments.*'  => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        $subcategory = Subcategory::findOrFail($data['subcategory_id']);

        if (strcasecmp($subcategory->name, 'Others') === 0 && empty($data['custom_issue'])) {
            return back()->withErrors(['custom_issue' => 'Describe the issue when choosing "Others".'])->withInput();
        }

        // Priority is derived server-side, never from user input.
        $isManagement = (bool) $user->is_management;
        $priority     = $isManagement ? 'critical' : ($subcategory->default_priority ?: 'medium');

        $tatHours = TatConfiguration::forPriority($priority)?->tat_hours ?? 8;

        $branchId = $data['branch_id'] ?? $user->branch_id;

        $ticket = Ticket::create([
            'ticket_number'  => Ticket::generateTicketNumber(),
            'support_type'   => $data['support_type'],
            'category_id'    => $data['category_id'],
            'subcategory_id' => $data['subcategory_id'],
            'branch_id'      => $branchId,
            'vendor_id'      => $data['support_type'] === 'infrastructure' ? ($data['vendor_id'] ?? null) : null,
            'subject'        => $data['subject'],
            'description'    => $data['description'] ?? null,
            'custom_issue'   => $data['custom_issue'] ?? null,
            'priority'       => $priority,
            'is_red_flag'    => $isManagement,
            'status'         => 'open',
            'created_by'     => $user->id,
            'employee_contact_name'  => $data['employee_contact_name']  ?? $user->name,
            'employee_contact_phone' => $data['employee_contact_phone'] ?? $user->phone,
            'employee_contact_email' => $data['employee_contact_email'] ?? $user->email,
            'tat_hours'      => $tatHours,
            'tat_deadline'   => now()->addHours($tatHours),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ((array) $request->file('attachments') as $file) {
                if (!$file) continue;
                $path = $file->store('attachments/' . $ticket->id, 'public');
                TicketAttachment::create([
                    'ticket_id'   => $ticket->id,
                    'uploaded_by' => $user->id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $path,
                    'file_size'   => $file->getSize(),
                    'mime_type'   => $file->getMimeType(),
                ]);
            }
        }

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'action_type' => 'created',
            'description' => 'Ticket created by ' . $user->name,
        ]);

        // Auto-assign based on region + support_type
        $assigned = app(TicketAssignmentService::class)->assign($ticket);
        if ($assigned) {
            $assigned->notify(new TicketAssignedNotification($ticket));
            // CC the TL (same support_type) if present and distinct
            $tl = User::where('role','resolver')->where('resolver_level','tl')
                ->where('assigned_support_type', $ticket->support_type)
                ->where('is_active', true)->first();
            if ($tl && $tl->id !== $assigned->id) {
                $tl->notify(new TicketAssignedNotification($ticket));
            }
        }

        // Management tickets: red-flag notify IT Head + TLs immediately
        if ($isManagement) {
            $escalators = User::where('role','resolver')
                ->whereIn('resolver_level', ['tl','it_head'])
                ->where('is_active', true)->get();
            Notification::send($escalators, new ManagementTicketNotification($ticket));
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket created: ' . $ticket->ticket_number);
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load([
            'creator','category','subcategory','assignee','branch.region','vendor',
            'activities.user','updates.user','updates.attachments',
            'expenses.addedBy','expenses.approvedBy','attachments.uploader',
        ]);

        // Unified timeline: newest first
        $timeline = collect()
            ->merge($ticket->activities->map(fn ($a) => (object) [
                'kind'        => 'activity', 'at' => $a->created_at, 'user' => $a->user,
                'type'        => $a->action_type, 'text' => $a->description,
                'old'         => $a->old_value, 'new' => $a->new_value,
                'attachments' => collect(),
            ]))
            ->merge($ticket->updates->map(fn ($u) => (object) [
                'kind'        => 'update', 'at' => $u->created_at, 'user' => $u->user,
                'type'        => 'update', 'text' => $u->note,
                'old'         => $u->status_from, 'new' => $u->status_to,
                'attachments' => $u->attachments,
            ]))
            ->sortByDesc('at')
            ->values();

        // Right-panel: only attachments NOT linked to an update (employee initial uploads)
        $initialAttachments = $ticket->attachments->whereNull('update_id')->values();

        return view('tickets.show', compact('ticket', 'timeline', 'initialAttachments'));
    }

    /**
     * Unified update form: either status, note, or both.
     */
    public function addUpdate(Request $request, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);

        $rules = [
            'status'         => 'nullable|in:' . implode(',', self::STATUSES),
            'note'           => 'nullable|string',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'file|max:10240',
        ];
        $data = $request->validate($rules);

        if (empty($data['status']) && empty($data['note']) && !$request->hasFile('attachments')) {
            return back()->withErrors(['note' => 'Enter a note, change the status, or upload a file.']);
        }

        $user      = $request->user();
        $statusOld = $ticket->status;
        $statusNew = $data['status'] ?? null;

        // Status transitions are gated: employees cannot change status
        if ($statusNew && $statusNew !== $statusOld) {
            if (! $request->user()->can('updateStatus', $ticket)) {
                return back()->withErrors(['status' => 'You are not allowed to change the status.']);
            }
            if ($statusNew === 'hold' && ! $request->user()->can('hold', $ticket)) {
                return back()->withErrors(['status' => 'Hold is only available for infrastructure tickets.']);
            }

            // Hold-aware TAT bookkeeping
            if ($statusOld === 'hold' && $statusNew !== 'hold') {
                TatCalculator::releaseHold($ticket);
            }
            if ($statusNew === 'hold' && $statusOld !== 'hold') {
                TatCalculator::beginHold($ticket);
            }

            $ticket->status = $statusNew;
            if ($statusNew === 'resolved' && !$ticket->resolved_at) $ticket->resolved_at = now();
            if ($statusNew === 'closed'   && !$ticket->closed_at)   $ticket->closed_at   = now();
            $ticket->save();

            if ($ticket->creator) {
                $ticket->creator->notify(new TicketStatusNotification($ticket, $statusOld, $statusNew));
            }
        }

        $ticketUpdate = TicketUpdate::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'status_from' => $statusNew && $statusNew !== $statusOld ? $statusOld : null,
            'status_to'   => $statusNew && $statusNew !== $statusOld ? $statusNew : null,
            'note'        => $data['note'] ?? null,
            'created_at'  => now(),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ((array) $request->file('attachments') as $file) {
                if (!$file) continue;
                $path = $file->store('attachments/' . $ticket->id, 'public');
                TicketAttachment::create([
                    'ticket_id'   => $ticket->id,
                    'update_id'   => $ticketUpdate->id,
                    'uploaded_by' => $user->id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $path,
                    'file_size'   => $file->getSize(),
                    'mime_type'   => $file->getMimeType(),
                ]);
            }
        }

        return back()->with('success', 'Update posted.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);
        $data = $request->validate(['assigned_to' => 'required|exists:users,id']);

        $ticket->update([
            'assigned_to' => $data['assigned_to'],
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
            'status'      => $ticket->status === 'open' ? 'assigned' : $ticket->status,
        ]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $request->user()->id,
            'action_type' => 'assigned',
            'description' => 'Ticket re-assigned to ' . $ticket->assignee->name,
            'new_value'   => $ticket->assignee->name,
        ]);

        $ticket->assignee->notify(new TicketAssignedNotification($ticket));

        return back()->with('success', 'Ticket assigned.');
    }

    public function addExpense(Request $request, Ticket $ticket)
    {
        $this->authorize('addExpense', $ticket);
        $data = $request->validate([
            'description'  => 'required|string|max:500',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'invoice'      => 'required|file|max:10240',
        ]);

        $path = $request->file('invoice')->store('expenses/' . $ticket->id, 'public');

        $expense = TicketExpense::create([
            'ticket_id'    => $ticket->id,
            'added_by'     => $request->user()->id,
            'description'  => $data['description'],
            'amount'       => $data['amount'],
            'expense_date' => $data['expense_date'],
            'invoice_path' => $path,
            'status'       => 'pending',
        ]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $request->user()->id,
            'action_type' => 'expense_added',
            'description' => 'Expense submitted for approval: ₹' . number_format($data['amount'], 2),
        ]);

        // Notify IT Head(s)
        $approvers = User::where('role','resolver')
            ->where('resolver_level','it_head')
            ->where('is_active', true)->get();
        Notification::send($approvers, new ExpenseSubmittedNotification($expense));

        return back()->with('success', 'Expense submitted for approval.');
    }

    public function addAttachment(Request $request, Ticket $ticket)
    {
        $this->authorize('attach', $ticket);
        $request->validate(['attachment' => 'required|file|max:10240']);
        $file = $request->file('attachment');
        $path = $file->store('attachments/' . $ticket->id, 'public');

        TicketAttachment::create([
            'ticket_id'   => $ticket->id,
            'uploaded_by' => $request->user()->id,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_size'   => $file->getSize(),
            'mime_type'   => $file->getMimeType(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function setVendorReference(Request $request, Ticket $ticket)
    {
        $this->authorize('updateStatus', $ticket);
        if ($ticket->support_type !== 'infrastructure' || !$ticket->vendor_id) {
            return back()->withErrors(['vendor_reference' => 'Vendor reference applies only to infrastructure tickets with a vendor set.']);
        }
        $data = $request->validate([
            'vendor_reference' => 'nullable|string|max:100',
        ]);

        $old = $ticket->vendor_reference;
        $new = $data['vendor_reference'] ?? null;
        if ($old === $new) {
            return back();
        }
        $ticket->update(['vendor_reference' => $new]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $request->user()->id,
            'action_type' => 'vendor_reference_updated',
            'description' => $new
                ? 'Vendor reference set to: ' . $new
                : 'Vendor reference cleared',
            'old_value'   => $old,
            'new_value'   => $new,
        ]);

        return back()->with('success', 'Vendor reference updated.');
    }

    public function toggleRedFlag(Request $request, Ticket $ticket)
    {
        $this->authorize('toggleRedFlag', $ticket);
        $old = $ticket->is_red_flag;
        $ticket->update(['is_red_flag' => !$old]);

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $request->user()->id,
            'action_type' => 'status_changed',
            'description' => ($old ? 'Red flag removed' : 'Ticket red-flagged') . ' by ' . $request->user()->name,
            'old_value'   => $old ? '1' : '0',
            'new_value'   => $old ? '0' : '1',
        ]);

        return back()->with('success', 'Red flag updated.');
    }

    public function export(Request $request)
    {
        if (!$request->user()->canExport()) abort(403);

        $filters = $request->only(['status','support_type','priority','is_red_flag','region_id','from','to']);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TicketsExport($request->user(), $filters),
            'tickets-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
