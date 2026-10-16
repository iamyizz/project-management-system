<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Set the interface locale from the signed-in user's preference.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?? 'id';

        if ($request->routeIs('locale.update') && $request->input('locale') === 'en') {
            $locale = 'en';
        }

        if ($request->query('print') === '1') {
            $locale = 'id';
        }

        App::setLocale(in_array($locale, ['id', 'en'], true) ? $locale : 'id');

        return $next($request);
    }
}
