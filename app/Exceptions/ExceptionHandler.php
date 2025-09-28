<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExceptionHandler extends Exception
{
    protected $message;

    protected $statusCode;

    public function __construct($message, $statusCode)
    {
        $statusCode = (is_int($statusCode) && ($statusCode > 0 && $statusCode <= 500)) ? $statusCode : Response::HTTP_INTERNAL_SERVER_ERROR;
        parent::__construct($message, $statusCode);

        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return trans('errors.get_category');
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return $this->apiResponse($this->message, $this->code);
        }

        return $this->webResponse($this->message);
    }

     /**
     * Handle Web response.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function webResponse($message)
    {
        return redirect()->back()->with('error', $message);
    }

    /**
     * Handle API response.
     *
     * @param  \Exception  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiResponse($message, $statusCode)
    {
        $statusCode = $statusCode ?? 500;
        $statusCode = (is_int($statusCode) && ($statusCode > 0 && $statusCode<=500)) ?$statusCode : Response::HTTP_INTERNAL_SERVER_ERROR;
        throw new HttpResponseException(response()->json([
            "message" => $message,
            "success" => false
        ], $statusCode));
    }
}
