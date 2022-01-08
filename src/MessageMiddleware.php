<?php

namespace Bfg\Emitter;

use Closure;

class MessageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @param  string  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, string $guard = "web")
    {
        if (!$guard) $guard = "web";

        if (!$request->hasHeader('X-Emitter-Message')) {

            abort(404);
        }

        if (\Auth::guard($guard)) {

            config(['auth.defaults.guard' => $guard]);
        }

        return $next($request);
    }
}
