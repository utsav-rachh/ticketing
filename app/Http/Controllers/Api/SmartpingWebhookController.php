<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Dialer\ProcessSmartpingWebhook;
use App\Models\DialerCallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives Smartping dialer webhooks. Every action MUST:
 *   - return 200 OK immediately (so Smartping doesn't retry),
 *   - persist the raw payload to dialer_call_logs,
 *   - hand the real processing to a queued job.
 *
 * Payload shapes are still TBD with Smartping (item #6 in the integration
 * reference). The queued job does best-effort matching by sessionId / phone.
 */
class SmartpingWebhookController extends Controller
{
    public function callStatus(Request $request) { return $this->accept('call_status', $request); }
    public function recording(Request $request)  { return $this->accept('recording_ready', $request); }
    public function incoming(Request $request)   { return $this->accept('incoming_call', $request); }
    public function missed(Request $request)     { return $this->accept('missed', $request); }

    private function accept(string $event, Request $request)
    {
        $payload = $request->all();

        try {
            // Unlinked log row — the job will attach it to a dialer ticket
            // once it resolves the sessionId / phone.
            DialerCallLog::create(['ticket_id' => null, 'event' => 'webhook:'.$event, 'data' => $payload]);
            ProcessSmartpingWebhook::dispatch($event, $payload);
        } catch (\Throwable $e) {
            Log::error("Smartping webhook [{$event}] could not be accepted", [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }
}
