<?php
namespace App\Jobs\Dialer;

use App\Models\DialerCustomer;
use App\Models\DialerTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

/**
 * Applies a Smartping webhook to the dialer tickets.
 *
 *   call_status     → update an existing (outbound) ticket: status, duration, recording.
 *   recording_ready → set recording_url on the matched ticket.
 *   incoming_call   → create an inbound ticket, match the caller to a customer.
 *   missed          → create (or flip to) a "missed" ticket.
 *
 * Smartping's exact field names are still TBD; we read a generous set of
 * likely keys (sessionId / session_id / callId, customerNumber / caller /
 * phone, recordingUrl / recording_url, etc.) and stash the raw payload on a
 * call-log row regardless.
 */
class ProcessSmartpingWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $event, public array $payload) {}

    public function handle(): void
    {
        match ($this->event) {
            'call_status'     => $this->handleCallStatus(),
            'recording_ready' => $this->handleRecording(),
            'incoming_call'   => $this->handleIncoming(),
            'missed'          => $this->handleMissed(),
            default           => null,
        };
    }

    // --- handlers --------------------------------------------------------

    private function handleCallStatus(): void
    {
        $ticket = $this->findOrCreateTicket(direction: $this->direction() ?? 'outbound');

        $status = $this->mapStatus($this->str(['status', 'callStatus', 'call_status', 'dialStatus']));
        $update = array_filter([
            'call_status' => $status,
            'duration'    => $this->int(['duration', 'callDuration', 'talkTime', 'billsec']),
            'recording_url' => $this->str(['recordingUrl', 'recording_url', 'recordingURL', 'recording']),
        ], fn ($v) => $v !== null);

        if ($update) $ticket->update($update);
        $ticket->logEvent('call_status_processed', $this->payload);
    }

    private function handleRecording(): void
    {
        $url    = $this->str(['recordingUrl', 'recording_url', 'recordingURL', 'recording', 'url']);
        $ticket = $this->findTicket();
        if (! $ticket) return;

        if ($url) $ticket->update(['recording_url' => $url]);
        $ticket->logEvent('recording_ready', $this->payload);
    }

    private function handleIncoming(): void
    {
        $phone    = DialerCustomer::normalizePhone($this->str(['customerNumber', 'caller', 'from', 'phone', 'callerNumber']));
        $customer = $phone !== '' ? DialerCustomer::where('phone', $phone)->first() : null;

        $ticket = DialerTicket::create([
            'ticket_number'     => DialerTicket::generateNumber(),
            'customer_id'       => $customer?->id,
            'customer_phone'    => $phone ?: null,
            'customer_name'     => $customer?->name,
            'direction'         => 'inbound',
            'call_status'       => 'ringing',
            'smartping_call_id' => $this->sessionId(),
        ]);
        $ticket->logEvent('incoming_call', $this->payload);
    }

    private function handleMissed(): void
    {
        $phone    = DialerCustomer::normalizePhone($this->str(['customerNumber', 'caller', 'from', 'phone', 'callerNumber']));
        $existing = $this->findTicket();

        if ($existing) {
            $existing->update(['call_status' => 'missed']);
            $existing->logEvent('missed', $this->payload);
            return;
        }

        $customer = $phone !== '' ? DialerCustomer::where('phone', $phone)->first() : null;
        $ticket = DialerTicket::create([
            'ticket_number'     => DialerTicket::generateNumber(),
            'customer_id'       => $customer?->id,
            'customer_phone'    => $phone ?: null,
            'customer_name'     => $customer?->name,
            'direction'         => $this->direction() ?? 'inbound',
            'call_status'       => 'missed',
            'smartping_call_id' => $this->sessionId(),
        ]);
        $ticket->logEvent('missed', $this->payload);
    }

    // --- matching helpers ------------------------------------------------

    private function findTicket(): ?DialerTicket
    {
        $sid = $this->sessionId();
        return $sid ? DialerTicket::where('smartping_call_id', $sid)->latest()->first() : null;
    }

    private function findOrCreateTicket(string $direction): DialerTicket
    {
        if ($t = $this->findTicket()) return $t;

        $phone    = DialerCustomer::normalizePhone($this->str(['customerNumber', 'caller', 'from', 'phone', 'callerNumber']));
        $customer = $phone !== '' ? DialerCustomer::where('phone', $phone)->first() : null;

        return DialerTicket::create([
            'ticket_number'     => DialerTicket::generateNumber(),
            'customer_id'       => $customer?->id,
            'customer_phone'    => $phone ?: null,
            'customer_name'     => $customer?->name,
            'direction'         => $direction,
            'call_status'       => 'initiated',
            'smartping_call_id' => $this->sessionId(),
        ]);
    }

    // --- payload readers -------------------------------------------------

    private function sessionId(): ?string
    {
        return $this->str(['sessionId', 'session_id', 'callId', 'call_id', 'uuid', 'uniqueId']);
    }

    private function direction(): ?string
    {
        $d = strtolower((string) $this->str(['direction', 'callType', 'type']));
        return match (true) {
            str_contains($d, 'out') => 'outbound',
            str_contains($d, 'in')  => 'inbound',
            default                 => null,
        };
    }

    private function mapStatus(?string $raw): ?string
    {
        if (! $raw) return null;
        $s = strtolower($raw);
        return match (true) {
            str_contains($s, 'answer'), str_contains($s, 'connect') => 'answered',
            str_contains($s, 'complete'), str_contains($s, 'end'), $s === 'completed' => 'completed',
            str_contains($s, 'miss'), str_contains($s, 'noanswer'), str_contains($s, 'no_answer') => 'missed',
            str_contains($s, 'busy') => 'busy',
            str_contains($s, 'fail'), str_contains($s, 'reject'), str_contains($s, 'cancel') => 'failed',
            str_contains($s, 'ring') => 'ringing',
            default => in_array($s, DialerTicket::STATUSES, true) ? $s : null,
        };
    }

    private function str(array $keys): ?string
    {
        foreach ($keys as $k) {
            $v = Arr::get($this->payload, $k);
            if (is_scalar($v) && trim((string) $v) !== '') return trim((string) $v);
        }
        return null;
    }

    private function int(array $keys): ?int
    {
        $v = $this->str($keys);
        return $v !== null && is_numeric($v) ? (int) $v : null;
    }
}
