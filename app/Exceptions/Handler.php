<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\Custom\AuthenticationException;
use App\Exceptions\Custom\AuthorizationException;
use App\Exceptions\Custom\ValidationException;

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

        $this->renderable(function (AuthenticationException $e, $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());
        });

        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], $e->getCode());
        });
    }
}