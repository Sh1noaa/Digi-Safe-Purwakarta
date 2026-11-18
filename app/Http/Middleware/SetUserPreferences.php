<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetUserPreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'id');
        $theme = session('theme', 'light');

        if (Auth::check()) {
            $user = Auth::user();
            if (! empty($user->locale) && in_array($user->locale, ['id', 'en'], true)) {
                $locale = $user->locale;
            }
            if (! empty($user->theme) && in_array($user->theme, ['light', 'dark'], true)) {
                $theme = $user->theme;
            }
            session(['locale' => $locale, 'theme' => $theme]);
        }

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }
        if (! in_array($theme, ['light', 'dark'], true)) {
            $theme = 'light';
        }

        App::setLocale($locale);
        \Carbon\Carbon::setLocale($locale);
        View::share('currentLocale', $locale);
        View::share('currentTheme', $theme);

        return $next($request);
    }
}

