<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPrimaryVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //if user has a primary_vendor_id, continue, otherwise send to vendor_selection view
        // if (! auth()->user()->hasRole($role)) {
        //     // Redirect...
        // }
        if (! auth()->user()->primary_vendor_id) {
            return redirect(route('vendor_selection'));
        }

        return $next($request);
    }
}
