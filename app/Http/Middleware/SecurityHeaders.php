<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()', false);
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups', false);
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none', false);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        // Cegah browser / komputer bersama (lab sekolah) menyimpan cache halaman autentikasi, OTP, profil & admin
        if ($request->is('login', 'register', 'lupa-password*', 'profile*', 'admin*', 'auth/google*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
