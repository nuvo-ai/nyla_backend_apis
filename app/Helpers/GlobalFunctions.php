<?php

use App\Constants\AppConstants;
use App\Helpers\Helper;
use Firebase\JWT\JWT;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Str;


function carbon()
{
    return new Carbon;
}

function slugify($value)
{
    return Str::slug($value);
}

function slugPermission(string $string)
{
    return 'can_'.str_replace('-', '_', slugify($string));
}

// function sudo()
// {
//     return Admin::where('email', env('SUDO_EMAIL', 'info@wealthapp.com'))->first();
// }

function formatMoney($amount, $places = 2, $symbol = '$')
{
    return Helper::formatMoney($amount, $places, $symbol);
}

function strLimit($string, $limit = 20, $end = '...')
{
    return Helper::strLimit($string, $limit, $end);
}

function formatDate($value)
{
    if (is_null($value) || empty($value)) {
        return $value;
    }

    if (! auth()->check()) {
        return Carbon::parse($value)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    return Helper::formatDateWithTimezone($value, auth()->user());
}

function report_error(\Exception $e){

    logger("Error Occurred:: " . json_encode([
        "line"      => $e->getLine(),
        "file"      => $e->getFile(),
        "message"   => $e->getMessage(),
        "trace"     => $e->getPrevious()
    ]));

    return null;
}

function uploadImage($file, $folder)
{
    if ($file) {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs($folder, $fileName, 'public');
        return "storage/$folder/$fileName";
    }
    return null;
}

function generateAppleClientSecret() {
    $privateKey = file_get_contents(storage_path('oauth/AuthKey_'.env('APPLE_KEY_ID').'.p8'));

    $payload = [
        'iss' => env('APPLE_TEAM_ID'),
        'iat' => time(),
        'exp' => time() + 86400 * 180, // 180 days
        'aud' => 'https://appleid.apple.com',
        'sub' => env('APPLE_CLIENT_ID'),
    ];

    return JWT::encode($payload, $privateKey, 'ES256', env('APPLE_KEY_ID'));
}



