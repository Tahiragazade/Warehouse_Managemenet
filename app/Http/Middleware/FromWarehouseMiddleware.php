<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class FromWarehouseMiddleware
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
        $store_id=$request->from_wh_id;
        if(isStorekeeper($store_id,Auth::id())==2)
        {
            return response()->json(['message' => 'You dont have Permission to do This'],403);
        }
        else {
            return $next($request);
        }
    }
}
