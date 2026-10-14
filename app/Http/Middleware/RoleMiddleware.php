<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->guest(route('login'));
        }

        if (! auth()->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Akun kamu sudah dinonaktifkan. Hubungi administrator.');
        }

        if (! in_array(auth()->user()->role, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
