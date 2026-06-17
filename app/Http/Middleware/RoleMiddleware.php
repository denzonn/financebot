<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->roles;

        if (!in_array($userRole, $roles)) {

            return match ($userRole) {
                'webmaster' => redirect()->route('dashboard'),
                'user'   => redirect()->route('user.dashboard'),
                default     => redirect('/'),
            };
        }

        return $next($request);
    }
}
