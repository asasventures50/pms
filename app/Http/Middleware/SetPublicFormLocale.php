<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicFormLocale
{
    public const SESSION_KEY = 'public_form_locale';

    /**
     * @var list<string>
     */
    private const ALLOWED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('lang')) {
            $lang = (string) $request->query('lang');
            if (in_array($lang, self::ALLOWED, true)) {
                $request->session()->put(self::SESSION_KEY, $lang);
            }
        }

        $locale = (string) $request->session()->get(self::SESSION_KEY, 'en');
        if (! in_array($locale, self::ALLOWED, true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
