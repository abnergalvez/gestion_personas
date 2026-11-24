<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuperAdmin()) {
            return $next($request);
        }

        $request->attributes->set('activity.denied', true);

        return redirect()
            ->route('dashboard')
            ->with('error', 'Los mantenedores solo los opera el super administrador.');
    }
}
