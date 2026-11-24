<?php

namespace App\Http\Middleware;

use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('activity.actor_id', $request->user()?->id);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            ActivityLogger::fromRequest($request, $response);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
