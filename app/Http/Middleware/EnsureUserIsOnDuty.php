<?php

namespace App\Http\Middleware;

use App\Models\DutyToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsOnDuty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $activeToken = DutyToken::latest()->first()?->token;
        if (
            (!$activeToken || $user->duty_token !== $activeToken)
            && !$user->role_names->intersect(config('app.specific_roles'))->count()
        ) {
            return redirect('duty-token-user-authorization');
        }

        return $next($request);
    }
}
