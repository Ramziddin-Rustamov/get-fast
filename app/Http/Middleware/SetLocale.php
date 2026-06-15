<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Qo'llab-quvvatlanadigan tillar. Default — uz.
     */
    public const SUPPORTED = ['uz', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'uz');

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'uz';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
