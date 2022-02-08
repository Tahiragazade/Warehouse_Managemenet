<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(isAdmin(Auth::id())==2)
        {
            return response()->json(['message' => 'You dont have Permission to do This'],403);
        }
        else {
            return $next($request);
        }
    }
}
