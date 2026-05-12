<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Direct role match (employee / resolver / admin / management / developer).
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Pseudo-role "it_head": an IT Head is a resolver with that level, but
        // we let route groups opt them in explicitly (e.g. the admin area).
        if (in_array('it_head', $roles, true) && $user->isITHead()) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
