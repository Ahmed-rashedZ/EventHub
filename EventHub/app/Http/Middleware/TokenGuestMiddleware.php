<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenGuestMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $tokenStr = $request->cookie('auth_token');
        if ($tokenStr) {
            $accessToken = PersonalAccessToken::findToken($tokenStr);
            if ($accessToken && $accessToken->tokenable) {
                $user = $accessToken->tokenable;
                $map = [
                    'Admin' => '/admin/dashboard',
                    'Event Manager' => '/manager/dashboard',
                    'Sponsor' => '/sponsor/dashboard',
                    'User' => '/profile',
                    'Assistant' => '/profile'
                ];
                return redirect($map[$user->role] ?? '/profile');
            }
        }
        return $next($request);
    }
}
