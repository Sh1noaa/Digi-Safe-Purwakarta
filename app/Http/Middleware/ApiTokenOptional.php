<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenOptional
{
    public function handle(Request $request, Closure $next): Response
    {
        ApiToken::resolveRequestContext($request);

        return $next($request);
    }
}

