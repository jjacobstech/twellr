<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Creative
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();
        if ($user->role == 'creative') {
            if (!empty($user->bank_name) || !empty($user->phone_no) || !empty($user->address) || !empty($user->account_name) || !empty($user->account_no)) {
                return $next($request);
            }
        }
        if ($user->role == 'user') {
            return $next($request);
        }
        if ($user->role == 'admin') {
            return $next($request);
        }
        return redirect(route('creative.payment.preference'));
    }
}
