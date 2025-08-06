<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            // Handle API routes specifically
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Throwable $e, $request)
    {
        // Authentication errors
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please provide a valid Bearer token.',
                'errors' => null,
                'code' => 401
            ], 401);
        }

        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors(),
                'code' => 422
            ], 422);
        }

        // Model not found errors
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found. Please check the ID and try again.',
                'errors' => null,
                'code' => 404
            ], 404);
        }

        // Route not found errors
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'API endpoint not found. Please check the URL and try again.',
                'errors' => null,
                'code' => 404
            ], 404);
        }

        // Method not allowed errors
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed for this endpoint.',
                'errors' => null,
                'code' => 405
            ], 405);
        }

        // Generic server errors
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again later.',
            'errors' => null,
            'code' => 500
        ], 500);
    }
}
