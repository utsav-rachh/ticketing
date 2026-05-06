<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Developers don't see the main app at all — any request from a developer
 * outside the /developer area is bounced to the sandbox home. The dev
 * routes themselves are gated by `role:developer`, so this middleware is
 * the inverse: keep developers in their sandbox.
 */
class RedirectDeveloperToSandbox
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        if ($user && $user->isDeveloper() && !$request->is('developer*') && !$request->is('logout')) {
            return redirect()->route('developer.home');
        }
        return $next($request);
    }
}
