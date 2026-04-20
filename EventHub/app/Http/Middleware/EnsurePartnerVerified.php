<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role, ['Event Manager', 'Sponsor'])) {
            if ($user->verification_status !== 'verified') {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Your account is pending verification by the administration. You will have full access once approved.',
                        'verification_status' => $user->verification_status,
                        'verification_notes' => $user->verification_notes,
                    ], 403);
                } else {
                    return redirect('/pending-verification');
                }
            }
        }

        return $next($request);
    }
}
