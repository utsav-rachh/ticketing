<?php
namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\DialerCustomer;
use App\Models\DialerTicket;
use App\Services\SmartpingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dialer landing screen + outbound call control. Developer-only.
 *
 * The actual calling happens inside Smartping's iframe; this controller's
 * `call`/`hangup` actions are the fallback "Click-to-Call" path described in
 * the integration reference (agent number + customer number → sessionId).
 */
class DialerController extends Controller
{
    public function __construct(private SmartpingService $smartping) {}

    public function index(Request $request)
    {
        $today = today();

        $stats = [
            'calls_today'     => DialerTicket::whereDate('created_at', $today)->count(),
            'connected_today' => DialerTicket::whereDate('created_at', $today)->where('call_status', 'completed')->count(),
            'missed_open'     => DialerTicket::missed()->count(),
            'customers'       => DialerCustomer::count(),
        ];

        $recentCalls = DialerTicket::with('customer')->latest()->limit(15)->get();

        return view('developer.dialer.index', [
            'stats'        => $stats,
            'recentCalls'  => $recentCalls,
            'iframeUrl'    => $this->smartping->iframeUrl(),
            'configured'   => $this->smartping->isConfigured(),
        ]);
    }

    /** Fallback Click-to-Call: dials a customer for the logged-in agent. */
    public function call(Request $request)
    {
        $data = $request->validate([
            'customer_id'     => ['nullable', 'exists:dialer_customers,id'],
            'customer_phone'  => ['required_without:customer_id', 'nullable', 'string', 'max:32'],
            'agent_number'    => ['required', 'string', 'max:32'],
        ]);

        $customer = isset($data['customer_id']) ? DialerCustomer::find($data['customer_id']) : null;
        $phone    = $customer?->phone ?: DialerCustomer::normalizePhone($data['customer_phone'] ?? '');

        if ($phone === '') {
            return back()->with('error', 'A customer phone number is required to place a call.');
        }

        $ticket = DB::transaction(function () use ($customer, $phone, $data, $request) {
            $t = DialerTicket::create([
                'ticket_number'  => DialerTicket::generateNumber(),
                'customer_id'    => $customer?->id,
                'customer_phone' => $phone,
                'customer_name'  => $customer?->name,
                'direction'      => 'outbound',
                'call_status'    => 'initiated',
                'agent_id'       => $request->user()->id,
                'agent_name'     => $request->user()->name,
            ]);
            $t->logEvent('call_initiated', ['agent_number' => $data['agent_number'], 'customer_phone' => $phone]);
            return $t;
        });

        $resp = $this->smartping->clickToCall($data['agent_number'], $phone);

        if (isset($resp['error'])) {
            $ticket->update(['call_status' => 'failed']);
            $ticket->logEvent('call_failed', $resp);
            return back()->with('error', match ($resp['error']) {
                'smartping_not_configured' => 'Smartping isn’t configured yet — set SMARTPING_API_KEY / SMARTPING_SME_ID.',
                default                    => 'Smartping rejected the call request. Check the logs.',
            });
        }

        $sessionId = $resp['sessionId'] ?? $resp['session_id'] ?? null;
        $ticket->update([
            'smartping_call_id' => $sessionId,
            'call_status'       => 'ringing',
        ]);
        $ticket->logEvent('clicktocall_accepted', $resp);

        return back()->with('success', "Calling {$phone}… ({$ticket->ticket_number})");
    }

    public function hangup(Request $request, DialerTicket $dialerTicket)
    {
        if ($dialerTicket->smartping_call_id) {
            $resp = $this->smartping->dropCall($dialerTicket->smartping_call_id);
            $dialerTicket->logEvent('drop_call_requested', $resp);
        }
        if ($dialerTicket->isLive()) {
            $dialerTicket->update(['call_status' => 'completed']);
        }
        return back()->with('success', "Ended {$dialerTicket->ticket_number}.");
    }
}
