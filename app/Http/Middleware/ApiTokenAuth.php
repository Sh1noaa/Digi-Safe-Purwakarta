<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        ApiToken::resolveRequestContext($request);

        if (! Auth::check()) {
            $isEn = app()->getLocale() === 'en';

            return response()->json([
                'success' => false,
                'message' => $isEn
                    ? 'Authentication required. Please sign in first.'
                    : 'Sesi login diperlukan. Silakan masuk terlebih dahulu.',
            ], 401);
        }

        return $next($request);
    }
}

