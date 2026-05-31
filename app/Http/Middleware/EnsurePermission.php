<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $allowed = collect(explode('|', $permission))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->contains(fn (string $name) => $user->hasPermission($name));

        if (! $allowed) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
