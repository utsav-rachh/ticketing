<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Validates that an inbound /api/smartping/* request really came from
 * Smartping. Two checks, either is sufficient:
 *   1. An API key passed in the `Authorization` (or `X-Smartping-Key`) header
 *      matching config('services.smartping.webhook_secret').
 *   2. The caller IP being in config('services.smartping.webhook_ips').
 *
 * Until Smartping confirms the exact mechanism (item #7 in the integration
 * reference), if NEITHER a secret nor an IP allowlist is configured we let
 * the request through so the endpoints stay testable.
 */
class VerifySmartpingWebhook
{
    public function handle(Request $request, Closure $next): mixed
    {
        $secret = config('services.smartping.webhook_secret');
        $ips    = array_filter(array_map('trim', explode(',', (string) config('services.smartping.webhook_ips'))));

        // Nothing configured yet → allow (dev / pre-integration).
        if (! $secret && ! $ips) {
            return $next($request);
        }

        if ($secret) {
            $provided = $request->header('X-Smartping-Key')
                ?? $request->bearerToken()
                ?? $request->header('Authorization');
            if (is_string($provided) && hash_equals($secret, trim(str_ireplace('Bearer ', '', $provided)))) {
                return $next($request);
            }
        }

        if ($ips && in_array($request->ip(), $ips, true)) {
            return $next($request);
        }

        abort(401, 'Unrecognised webhook origin.');
    }
}
