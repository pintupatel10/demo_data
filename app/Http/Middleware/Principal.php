<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Principal
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
        if (Auth::user()->staff_role == 'principal' || Auth::user()->role == 'admin')
        {
            $uri = $request->path();
        }
        else{
            return redirect('dashboard');
        }
        return $next($request);
    }
}
