<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->hasAdminAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Fitur ini khusus Admin dan Owner.',
            ], 403);
        }

        return $next($request);
    }
}

