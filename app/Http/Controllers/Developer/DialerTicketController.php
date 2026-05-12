<?php
namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\DialerTicket;
use Illuminate\Http\Request;

/**
 * Dialer tickets — the master call log plus per-call detail (call trail +
 * recording playback + notes). One row per call; auto-created by outbound
 * Click-to-Call and by inbound / missed webhooks. Developer-only.
 */
class DialerTicketController extends Controller
{
    public function index(Request $request)
    {
        $direction = $request->get('direction');
        $status    = $request->get('status');
        $q         = trim((string) $request->get('q'));

        $tickets = DialerTicket::query()
            ->with('customer')
            ->when(in_array($direction, DialerTicket::DIRECTIONS, true), fn ($x) => $x->where('direction', $direction))
            ->when(in_array($status, DialerTicket::STATUSES, true), fn ($x) => $x->where('call_status', $status))
            ->when($q !== '', function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('ticket_number', 'like', "%{$q}%")
                      ->orWhere('customer_name', 'like', "%{$q}%")
                      ->orWhere('customer_phone', 'like', "%{$q}%")
                      ->orWhere('agent_name', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('developer.dialer.tickets.index', compact('tickets', 'direction', 'status', 'q'));
    }

    public function show(DialerTicket $dialerTicket)
    {
        $dialerTicket->load(['customer', 'agent', 'logs']);
        return view('developer.dialer.tickets.show', ['ticket' => $dialerTicket]);
    }

    public function updateNotes(Request $request, DialerTicket $dialerTicket)
    {
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:5000']]);
        $dialerTicket->update(['notes' => $data['notes'] ?? null]);
        return back()->with('success', 'Notes saved.');
    }
}
