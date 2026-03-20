<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->user();

        if ($auth === null) {
            return redirect('/');
        }

        if ((int) $auth->is_admin !== 1) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
