<?php

namespace Abs\BasicPkg\Exceptions;

use ApiResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        NotFoundHttpException::class,
        //FileNotFoundException::class,
        UserFriendlyException::class,
        ValidationException::class,
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $e)
    {
		parent::report($e);
    }

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  Request  $request
	 * @param  Exception  $e
	 *
	 * @return Response
	 */
    public function render($request, Exception $e)
    {
		if ($e instanceof UserFriendlyException) {
			$output = new ApiResponse();
			$output->setError($e->getMessages());
			$output->setHttpStatus($e->getHttpStatus());

			return $output->response();
		}

		if ($e instanceof ValidationException) {
			$output = new ApiResponse();
			$output->setError($e);
			$output->setHttpStatus(400);
			//\Log::info((array)$output);
			return $output->response();
		}

		if ($e instanceof NotFoundHttpException) {
			if (strpos($request->path(), 'api/') === 0) {
				return parent::render($request, $e);
			}
			//else {
			//	return Response::make(view('angular.index'));
			//}
		}

		if ($request->route() && in_array('api', $request->route()
				->middleware())
		) {
			$output = new ApiResponse();
			$output->setError('Error: '.$e->getMessage().'. File: '.$e->getFile().'. Line: '.$e->getLine());
			$output->setHttpStatus(400);
			//if (config('app.debug')) {
			//	$output->setError('Error: '.$e->getMessage().'. File: '.$e->getFile().'. Line: '.$e->getLine());
			//}
			//else {
			//	$output->setError('Something went wrong');
			//}
			return $output->response();
		}
		else {
			return parent::render($request, $e);
		}
    }

    protected function unauthenticated($request, AuthenticationException $exception)
	{
		if ($request->expectsJson()) {
			return response()->json(['error' => 'Unauthenticated.'], 401);
		}

		return redirect()->guest('login');
	}

}
