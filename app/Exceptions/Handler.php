<?php

namespace App\Exceptions;

use App\Exceptions\ExceptionHandler as CustomExceptionHandler;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Exception $exception, $request) {

            if ($exception instanceof NotFoundHttpException) {

                if ($request->hasHeader('Accept-Lang')) {
                    app()->setLocale($request->header('Accept-Lang'));
                }

                throw new CustomExceptionHandler($exception->getMessage(), 400);
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception)) {
            if ($exception->getStatusCode() == 404) {
                return response()->view('errors.404', [], 404);
            }
        }
        return parent::render($request, $exception);
    }

}
