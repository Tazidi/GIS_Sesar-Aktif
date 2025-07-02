<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectToRoleDashboard
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            return match ($role) {
                'admin' => redirect('/maps'),
                'editor' => redirect('/articles'),
                default => redirect('/')
            };
        }

        return $next($request);
    }
}
