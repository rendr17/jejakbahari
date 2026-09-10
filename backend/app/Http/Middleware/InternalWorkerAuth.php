<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalWorkerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.internal_worker_token');

        if (! $expected) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_AUTH_FAILED',
                    'message' => 'Internal token not configured.',
                    'details' => null,
                ],
            ], 500);
        }

        $token = $request->bearerToken();

        if (! $token || ! hash_equals($expected, $token)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_AUTH_FAILED',
                    'message' => 'Invalid or missing internal token.',
                    'details' => null,
                ],
            ], 401);
        }

        return $next($request);
    }
}
