<?php

namespace App\Services\Auth;

class SanctumService
{
    const SESSION_KEY = "sanctum_auth_token";

    public static function getUserToken($request)
    {
        $token = $request->bearerToken();
        return $token;
    }

    public static function flushInvalidTokens($user, $request)
    {
        $currentToken = $request->user()->currentAccessToken();
        $user?->tokens()->where('id', '!=', $currentToken->id)->delete();
    }
}
