<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureOtpVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('otp_verified')) {
            return redirect()->route('otp.verify');
        }

        return $next($request);
    }
}
