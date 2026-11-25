<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekRole
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->hasRole($role)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}