<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Referred
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->referred_by != null) {
            $referred_by = Auth::user()->referred_by;
            $referral = Referral::where('referrer_id', $referred_by)
                ->where('referred_id', Auth::user()->id)
                ->update([
                    'status' => 'converted',
                    'converted_at' => now(),
                    'reward_points' => 2
                ]);

            if ($referral) {
                return $next($request);
            }
        }
        return $next($request);
    }
}
