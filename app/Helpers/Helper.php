<?php

namespace App\Helpers;

use App\Constants\AppConstants;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Helper
{
    // function to convert time to 12 hour format
    public static function time24hrs($time)
    {
        $time = date('g:i A', strtotime($time));

        return $time;
    }

    // explode address into two parts and return with <br> and ,
    public static function explodeAddress($address)
    {
        $address = explode(',', $address);
        $address = implode(', <br>', $address);

        return $address;
    }

    // function that convert date from dd-mm-yyyy to Jan 01, 2019 format
    public static function dateFormat($date)
    {
        $date = date('M d, Y', strtotime($date));

        return $date;
    }

    // function that convert sentence to url format and make it all small letters
    public static function sentenceToUrl($sentence)
    {
        $sentence = strtolower($sentence);
        $sentence = str_replace(' ', '-', $sentence);

        return $sentence;
    }

    // reverse function of sentenceToUrl and make all first letter capital
    public static function urlToSentence($url)
    {
        $url = ucwords($url);
        $url = str_replace('-', ' ', $url);

        return $url;
    }

    // function to return first letter of string in capital
    public static function firstLetterCapital($string)
    {
        $string = ucfirst($string);

        return $string;
    }

    public static function encrypt(string $string)
    {
        return self::encryptDecrypt('encrypt', $string);
    }

    public static function decrypt(string $string)
    {
        return self::encryptDecrypt('decrypt', $string);
    }

    private static function encryptDecrypt($action, $string)
    {
        try {
            $output = false;

            $encrypt_method = 'AES-256-CBC';
            $secret_key = 'Hg99JHShjdfhjhejkse@14447DP';
            $secret_iv = 'T0EHVn0dUIK888JSBGDD';

            // hash
            $key = hash('sha256', $secret_key);

            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $secret_iv), 0, 16);

            if ($action == 'encrypt') {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } elseif ($action == 'decrypt') {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }

            return $output;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function withDir($dir)
    {
        if (! is_dir($dir)) {
            mkdir(trim($dir), 0777, true);
        }
    }

    /**Reads file from private storage */
    public static function getFileFromPrivateStorage($fullpath, $disk = 'local')
    {
        if ((explode('/', $fullpath)[0] ?? '') === 'app') {
            $fullpath = str_replace('app/', '', $fullpath);
        }
        if ($disk == 'public') {
            $disk = null;
        }
        $exists = Storage::disk($disk)->exists($fullpath);
        if ($exists) {
            $fileContents = Storage::disk($disk)->get($fullpath);
            $content = Storage::mimeType($fullpath);
            $response = Response::make($fileContents, 200);
            $response->header('Content-Type', $content);

            return $response;
        }

        return null;
    }

    public static function deleteFileFromPrivateStorage($path, $disk = 'local')
    {
        if ((explode('/', $path)[0] ?? '') === 'app') {
            $path = str_replace('app/', '', $path);
        }

        $exists = Storage::disk($disk)->exists($path);
        if ($exists) {
            Storage::delete($path);
        }

        return $exists;
    }

    /**
     * @param  $mode  = ["encrypt" , "decrypt"]
     * @param  $path  =
     */
    public static function readFileUrl($mode, $path)
    {
        if (strtolower($mode) == 'encrypt') {
            $path = base64_encode($path);

            return route('web.read_file', $path);
        }

        return base64_decode($path);
    }

    // public static function sudo()
    // {
    //     return User::firstOrCreate([
    //         'role' => AppConstants::ROLE_SUDO,
    //     ], [
    //         'first_name' => 'Super',
    //         'last_name' => 'Admin',
    //         'email' => env('SUDO_EMAIL'),
    //         'password' => env('SUDO_PASSWORD'),
    //     ]);
    // }

    /** Returns a random alphanumeric token or number
     * @param int length
     * @param bool  type
     * @return string token
     */
    public static function getRandomToken($length, $typeInt = false)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet .= strtolower($codeAlphabet);
        $codeAlphabet .= '0123456789';
        $max = strlen($codeAlphabet);

        if ($typeInt == true) {
            for ($i = 0; $i < $length; $i++) {
                $token .= rand(0, 9);
            }
            $token = intval($token);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $token .= $codeAlphabet[random_int(0, $max - 1)];
            }
        }

        if (strlen(strval($token)) < $length) {
            return self::getRandomToken($length, $typeInt);
        }

        return $token;
    }

    public static function generateRandomDigits($length, $string = false)
    {
        $min = (int) pow(10, $length - 1);
        $max = (int) (pow(10, $length) - 1);

        $digits = random_int($min, $max);

        if ($string == true) {
            return (string) $digits;
        } else {
            return (int) $digits;
        }
    }

    /**Returns formatted money value
     * @param float amount
     * @param int places
     * @param string symbol
     */
    public static function formatMoney($amount, $places = 2, $symbol = '₦')
    {
        return $symbol.''.self::intFormat((float) $amount, $places);
    }

    public static function intFormat($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        $negation = ($number < 0) ? (-1) : 1;
        $coefficient = 10 ** $decimals;
        $number = $negation * floor((string) (abs($number) * $coefficient)) / $coefficient;

        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }

    public static function isProductionEnv()
    {
        return env('APP_ENV') == 'production';
    }

    public static function dispatchJob(ShouldQueue $job)
    {
        if (self::isProductionEnv()) {
            return dispatch($job);
        } else {
            return dispatch_sync($job);
        }
    }

    public static function dispatchJobSync(ShouldQueue $job)
    {
        return dispatch_sync($job);
    }

    public static function strLimit($string, $limit = 20, $end = '...')
    {
        return Str::limit(strip_tags($string), $limit, $end);
    }

    public static function formatDateWithTimezone($time, $user)
    {
        if (! empty($user->timezone)) {
            return Carbon::parse($time)->setTimezone($user->timezone)->format('Y-m-d H:i:s');
        } else {
            return Carbon::parse($time)->setTimezone('UTC')->format('Y-m-d H:i:s');
        }
    }

    public static function userNotificationPreference($user)
    {
        $preference = [];
        $notification_preference = $user->notificationPreference ?? null;

        if ($user?->can_receive_sms == 1) {
            $preference[] = "sms";
        }

        if ($notification_preference?->can_receive_push == 1) {
            $preference[] = 'firebase';
        }

        if ($notification_preference?->can_receive_mail == 1) {
            $preference[] = 'mail';
        }

        $preference[] = 'database';

        return $preference;
    }

    public static function getNames($fullName)
    {
        $nameParts = explode(' ', $fullName);

        $firstName = array_shift($nameParts);
        $lastName = array_pop($nameParts);
        $middleName = implode(' ', $nameParts);

        $result = [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];

        if (!empty($middleName)) {
            $result['middle_name'] = $middleName;
        }

        return $result;
    }
}
