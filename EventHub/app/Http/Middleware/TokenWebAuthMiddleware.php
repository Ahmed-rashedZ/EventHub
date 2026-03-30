<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class TokenWebAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $tokenStr = $request->cookie('auth_token');
        if (!$tokenStr) {
            return redirect('/login');
        }

        $accessToken = PersonalAccessToken::findToken($tokenStr);
        if (!$accessToken || !$accessToken->tokenable) {
            return redirect('/login')->withCookie(cookie()->forget('auth_token'));
        }

        $user = $accessToken->tokenable;

        // Login the user in the session guard so blade views can use auth()->user()
        Auth::login($user);

        // Check if role is required
        if (!empty($roles) && !in_array($user->role, $roles)) {
            // Unauthorised role, redirect to appropriate dash
            $map = [
                'Admin' => '/admin/dashboard',
                'Event Manager' => '/manager/dashboard',
                'Sponsor' => '/sponsor/dashboard',
                'User' => '/profile',
                'Assistant' => '/profile'
            ];
            $target = $map[$user->role] ?? '/login';
            return redirect($target);
        }

        return $next($request);
    }
}
