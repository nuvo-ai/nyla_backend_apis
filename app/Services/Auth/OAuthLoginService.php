<?php

namespace App\Services\Auth;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Services\General\Guzzle\GuzzleService;
use AppleSignIn\ASDecoder;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthLoginService
{
    private $token;
    private $provider;

    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    public function setProvider(string $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    public function byProvider()
    {
        if ($this->provider == "google") {
            return self::withGoogle();
        }

        if ($this->provider == "apple") {
            return self::withApple();
        }

        if ($this->provider == "facebook") {
            return self::withFacebook();
        }

        if ($this->provider == "tiktok") {
            return self::withTiktok();
        }
    }

    public function withGoogle()
    {
        try {
            $token = $this->token;

            $user_data = Socialite::driver($this->provider)->userFromToken($token);

            if (empty($user_data)) {
                throw new AuthException("Unable to validate token");
            }

            if ($user_data == false) {
                throw new AuthException("The token has expired or is invalid.");
            }

            $payload = [
                "email" => $user_data->email,
                "name" => trim($user_data->user['given_name'] . " " . ($user_data->user['family_name'] ?? null)),
            ];

            return $payload;
        } catch (Throwable $e) {
            logger()->error("Error -oauthLoginController", [
                "message" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function withApple()
    {
        try {
            $oauth = ASDecoder::getAppleSignInPayload($this->token);
            $email = $oauth->getEmail();
            $user = $oauth->getUser();
            $is_valid = $oauth->verifyUser($user);

            if (!$is_valid) {
                throw new AuthException("The token has expired or is invalid.");
            }

            return [
                "name" => explode("@", $email)[0],
                "email" => $email
            ];
        } catch (Throwable $e) {
            logger()->error("Error -oauthLoginController", [
                "message" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function withFacebook()
    {
        try {
            $token = $this->token;

            $user_data = Socialite::driver($this->provider)->stateless()->userFromToken($token);

            if (empty($user_data)) {
                throw new AuthException("Unable to validate token");
            }

            $data = [
                "name" => $user_data->name,
                "email" => $user_data->email,
                "social_id" => $user_data->getId(),
                "avatar" => $user_data->avatar ?? null
            ];

            return $data;
        } catch (Throwable $e) {
            logger()->error("Error -oauthLoginController", [
                "message" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function withTiktok()
    {
        try {
            $auth_code = $this->token;

            $response = (new GuzzleService)
                ->post("https://open-api.tiktok.com/oauth/access_token/", [
                    "client_key" => config("services.tiktok.client_id"),
                    "client_secret" => config("services.tiktok.client_secret"),
                    "code" => $auth_code,
                    "grant_type" => "authorization_code",
                ]);

            if (!in_array($response["status"], [ApiConstants::GOOD_REQ_CODE])) {
                throw new AuthException($response["message"]["error"] ?? $response["data"]["data"]["description"] ??  "Request failed");
            }

            if (in_array($response["data"]["message"], ["error"])) {
                throw new AuthException($response["data"]["data"]["description"] ??  "Request failed");
            }

            $token = $response["data"]["data"]["access_token"] ?? null;
            $user_data = Socialite::driver($this->provider)->userFromToken($token);

            if (empty($user_data)) {
                throw new AuthException("Unable to validate token");
            }

            $data = [
                "name" => $user_data->name,
                "email" => $user_data->email ??  "$user_data->name@tiktok.com",
                "social_id" => $user_data->id,
            ];

            return $data;
        } catch (Throwable $e) {
            logger()->error("Error -oauthLoginController", [
                "message" => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
