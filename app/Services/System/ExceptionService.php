<?php

namespace App\Services\System;

use Exception;
use Illuminate\Support\Facades\Log;

class ExceptionService
{
    public static function broadcastOnAllChannels(Exception $e)
    {
        Log::channel("broadcast_exception")->info($e->getMessage(), $e->getTrace());
    }

    public static function externalChannels(Exception $e)
    {
        Log::channel("external_exception")->info($e->getMessage(), $e->getTrace());
    }

    public static function log(Exception $e)
    {
        Log::channel("log_exception")->info($e->getMessage(), $e->getTrace());
    }

    public static function logAndBroadcast(Exception $e)
    {
        Log::channel("log_exception")->info($e->getMessage(), $e->getTrace());
        Log::channel("broadcast_exception")->info($e->getMessage(), $e->getTrace());
    }

    public static function defaultChannel(Exception $e)
    {
        Log::info($e->getMessage(), $e->getTrace());
    }
}
