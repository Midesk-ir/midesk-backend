<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');

        if (app()->bound('tenant')) {
            $locale = $locale ?: app('tenant')->locale;
        }

        $locale = in_array($locale, ['fa', 'en'], true) ? $locale : 'fa';

        App::setLocale($locale);

        return $next($request);
    }
}
