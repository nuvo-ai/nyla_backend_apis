<?php

namespace App\Helpers;

use App\Constants\General\ApiConstants;
use App\Constants\General\NotificationConstants;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ApiHelper
{
    const SERVER_ERROR_MESSAGE = 'Failed to process your request. Kindly try again after a while or contact our support team.';

    public static function throwableResponse(Throwable $e, ?Request $request = null)
    {
        logger('Throwable', [$e->getMessage(), $e->getTrace()]);

        if ($e instanceof HttpException) {
            throw $e;
        }

        // Handle authentication issues specifically
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated',
                'code' => 401
            ], 401);
        }

        if (!empty($request) && $request->expectsJson()) {
            return ApiHelper::problemResponse(
                self::SERVER_ERROR_MESSAGE,
                ApiConstants::SERVER_ERR_CODE
            );
        }

        // Fallback for unexpected scenarios — still return a JSON response
        return response()->json([
            'message' => $e->getMessage(),
            'code' => ApiConstants::SERVER_ERR_CODE,
        ], 500);
    }


    public static function problemResponse(
        ?string $message,
        ?int $status_code,
        ?Request $request = null,
        ?Throwable $trace = null
    ) {
        $code = ! empty($status_code) ? $status_code : ApiConstants::BAD_REQ_ERR_CODE;
        $traceMsg = empty($trace) ? null : $trace->getMessage();

        $body = [
            'message' => $message,
            'code' => $code,
            'success' => false,
            'error_debug' => $traceMsg,
        ];

        ! empty($trace) ? logger($trace->getMessage(), $trace->getTrace()) : null;
        if (! empty($trace)) {
            // \Sentry\captureException($trace);
            $body['trace'] = $trace->getTrace();
        }

        return response()->json($body)->setStatusCode($code);
    }

    /** Return error api response */
    public static function inputErrorResponse(?string $message = null, ?int $status_code = null, ?Request $request = null, ?ValidationException $trace = null)
    {
        $code = ($status_code != null) ? $status_code : '';

        $body = [
            'message' => $message,
            'code' => $code,
            'success' => false,
            'errors' => empty($trace) ? null : $trace->errors(),
        ];

        if (! empty($trace)) {
            // \Sentry\captureException($trace);
        }

        return response()->json($body)->setStatusCode($code);
    }

    /** Return valid api response */
    public static function validResponse(?string $message = null, $data = null, $request = null, $code = null)
    {
        if (is_null($data) || empty($data)) {
            $data = null;
        }
        $body = [
            'message' => $message,
            'data' => $data,
            'success' => true,
            'code' => $code ?? ApiConstants::GOOD_REQ_CODE,

        ];

        return response()->json($body)->setStatusCode($body['code']);
    }

    public static function validData(?string $message = null, $data = null, $terminus = null)
    {
        if (is_null($data) || empty($data)) {
            $data = [];
        }

        $body = [
            'message' => $message,
            'code' => ApiConstants::GOOD_REQ_CODE,
            'success' => false,
            'errors' => empty($trace) ? null : $trace->errors(),
        ];

        return $body;
    }

    public static function problemData(?string $message, int $status_code, ?Exception $trace = null, $terminus = null)
    {
        $code = ! empty($status_code) ? $status_code : null;
        $traceMsg = empty($trace) ? null : $trace->getMessage();

        $body = [
            'message' => $message,
            'code' => $code,
            'success' => false,
            'error_debug' => $traceMsg,
        ];

        return $body;
    }

    public static function inputErrorData(?string $message = null, ?int $status_code = null, ?ValidationException $trace = null, $terminus = null)
    {
        $code = ($status_code != null) ? $status_code : '';

        $body = [
            'message' => $message,
            'code' => $code,
            'success' => false,
            'errors' => empty($trace) ? null : $trace->errors(),
        ];

        return $body;
    }

    /**Returns formatted money value
     * @param float amount
     * @param int places
     * @param string symbol
     */

    /**Returns formatted date value
     * @param string date
     * @param string format
     */
    public static function formatDate($date, $format = 'Y-m-d')
    {
        return date($format, strtotime($date));
    }

    /**Returns the available auth instance with user
     * @param bool $getUser
     */
    public static function auth($getUser = false)
    {
        return $getUser ? auth('api')->user() : auth('api');
    }

    public static function collectPagination(LengthAwarePaginator $pagination, $appendQuery = true)
    {
        $request = request();
        unset($request['token']);
        if ($appendQuery) {
            $pagination->appends($request->query());
        }
        $all_pg_data = $pagination->toArray();
        unset($all_pg_data['links']); // remove links
        unset($all_pg_data['data']); // remove old data mapping

        $buildResponse['pagination_meta'] = $all_pg_data;
        $buildResponse['pagination_meta']['can_load_more'] = $all_pg_data['to'] < $all_pg_data['total'];
        $buildResponse['data'] = $pagination->getCollection();

        return $buildResponse;
    }


}
