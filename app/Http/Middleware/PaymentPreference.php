<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PaymentPreference
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
                session()->put('user', $user);
                return redirect(route('dashboard',  false));
            }
        }
        if ($user->role == 'user') {
            return redirect(route('dashboard', absolute: false));
        }
        return $next($request);
    }
}