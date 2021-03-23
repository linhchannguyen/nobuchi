<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class isSupplier
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
        if(auth()->user()->type == 2){
            return $next($request);
        }
        else {
            if($request->ajax())
            {
                return response('Khong co quyen truy cap', 405);
            }
            return redirect('/');
        }
    }
}
