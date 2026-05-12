<?php
namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for Smartping's CRM-integration API (Click-to-Call, Drop-Call,
 * Add/Update Agent). Several endpoints/payloads are still TBD with Smartping —
 * see the integration reference. Calls return the decoded JSON body, or an
 * ['error' => ...] array on transport/HTTP failure so callers never blow up.
 */
class SmartpingService
{
    public function isConfigured(): bool
    {
        return filled(config('services.smartping.api_key'))
            && filled(config('services.smartping.base_url'));
    }

    public function iframeUrl(): ?string
    {
        return config('services.smartping.iframe_url') ?: null;
    }

    /**
     * Agent initiates an outbound call. Returns Smartping's response which is
     * expected to contain a sessionId we store on the dialer ticket.
     */
    public function clickToCall(string $agentNumber, string $customerNumber): array
    {
        return $this->post('/click-to-call', [
            'smeId'          => config('services.smartping.sme_id'),
            'agentNumber'    => $agentNumber,
            'customerNumber' => $customerNumber,
        ]);
    }

    public function dropCall(string $sessionId): array
    {
        return $this->post('/drop-call', ['sessionId' => $sessionId]);
    }

    public function addAgent(array $agentData): array
    {
        return $this->post('/add-agent', $agentData);
    }

    public function updateAgent(array $agentData): array
    {
        return $this->put('/update-agent', $agentData);
    }

    // --- internals -------------------------------------------------------

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.smartping.base_url'), '/'))
            ->withHeaders(['Authorization' => (string) config('services.smartping.api_key')])
            ->acceptJson()
            ->timeout(15);
    }

    private function post(string $path, array $payload): array
    {
        return $this->send('post', $path, $payload);
    }

    private function put(string $path, array $payload): array
    {
        return $this->send('put', $path, $payload);
    }

    private function send(string $method, string $path, array $payload): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'smartping_not_configured'];
        }

        try {
            $res = $this->client()->{$method}($path, $payload);
            if ($res->failed()) {
                Log::warning('Smartping API error', ['path' => $path, 'status' => $res->status(), 'body' => $res->body()]);
                return ['error' => 'http_'.$res->status(), 'body' => $res->json() ?? $res->body()];
            }
            return $res->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('Smartping API request failed', ['path' => $path, 'error' => $e->getMessage()]);
            return ['error' => 'request_failed', 'message' => $e->getMessage()];
        }
    }
}
