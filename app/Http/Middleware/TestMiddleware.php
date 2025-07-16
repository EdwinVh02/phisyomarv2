<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        dd('¡TestMiddleware funciona!');
        return $next($request);
    }
}
