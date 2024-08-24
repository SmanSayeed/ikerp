<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception): JsonResponse
    {


        if ($exception instanceof ValidationException) {
            return ResponseHelper::error('Validation failed', 422, $exception->errors());
        }

        if ($exception instanceof HttpException) {
            return ResponseHelper::error($exception->getMessage(), $exception->getStatusCode());
        }

        return ResponseHelper::error('An error occurred: ' . $exception->getMessage(), 500);
    }
}
