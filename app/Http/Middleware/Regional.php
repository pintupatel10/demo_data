<?php

namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class Regional
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
        if (Auth::user()->role == 'regional' || Auth::user()->role == 'admin')
        {
            $uri = $request->path();
            if (Auth::user()->role == 'regional' && $uri=="regional/users"){
                return redirect('dashboard');
            }
        }
        else{
            return redirect('dashboard');
        }
        return $next($request);
    }
}
