<?php

namespace App\Http\Middleware;

use Arcanedev\LogViewer\Entities\Log;
use Illuminate\Support\Facades\Auth;
use Closure;

class isAdmin
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
        if(auth()->user()->type == 1 || auth()->user()->type == 0)
        {
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
