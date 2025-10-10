<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $request->bearerToken()) {
            $token = $user->currentAccessToken();

            if ($token && $token->expires_at && $token->expires_at->isPast()) {
                $token->delete();
                throw new AuthenticationException('Token 已過期，請重新登入');
            }
        }

        return $next($request);
    }
}
