<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // cek autentikasi dan pastikan role adalah admin atau owner
        if (! Auth::check() || ! in_array(Auth::user()->role, ['admin', 'owner'], true)) {
            abort(403, 'Akses ditolak. Halaman ini hanya dapat diakses oleh Admin dan Owner.');
        }

        return $next($request);
    }
}
