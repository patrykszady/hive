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
        // if (! auth()->user()->hasRole($role)) {
        //     // Redirect...
        // }
        $user = auth()->user();
        // dd($user);

        //if user has a primary_vendor_id = continue, otherwise send to vendor_selection view, if vendor is not registered yet
        if(!$user->primary_vendor_id) {
            return redirect(route('vendor_selection'));
        }

        //if $user->primary_vendor_id (above if) AND !$user->vendor->registration['registered'] (below if)
        if(!$user->vendor->registration['registered']){
            // return redirect(route('vendor_registration', $user->vendor->id));
            return redirect(route('vendor_selection'));
        }

        return $next($request);
    }
}
