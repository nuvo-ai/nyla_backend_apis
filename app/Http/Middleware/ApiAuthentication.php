<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please provide a valid Bearer token.',
                    'errors' => null,
                    'code' => 401
                ], 401);
            }

            return $next($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please provide a valid Bearer token.',
                'errors' => null,
                'code' => 401
            ], 401);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'errors' => null,
                'code' => 500
            ], 500);
        }
    }
}
