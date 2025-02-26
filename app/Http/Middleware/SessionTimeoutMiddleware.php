<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');

            // Check if the user has been inactive for more than 30 minutes
            if ($lastActivity && now()->diffInMinutes($lastActivity) > 30) {
                Auth::logout(); // Log out user
                session()->flush(); // Clear session

                return response()->json(['message' => 'Session expired. Please log in again.'], 401);
            }

            // Update last activity time
            session(['last_activity' => now()]);
        }

        return $next($request);
    }
}
