<?php
namespace Abs\BasicPkg\Controllers\Api;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Response;
use View;

class BaseController extends Controller
{

	/**
	 * @param  boolean  $status
	 * @param  array|null  $data
	 * @param  null  $errorMessage
	 *
	 * @return JsonResponse
	 * @throws Exception
	 */
	protected function response($status, array $data = null, $errorMessage = null, $bulkOperationResults = null)
	{
		$response = new ApiResponse();

		if (!is_null($data)) {
			$response->setDataArray($data);
		}

		if (!is_null($errorMessage)) {
			$response->setError($errorMessage);
		}
		
		if (!is_null($bulkOperationResults)) {
			$response->setBulkOperationResults($bulkOperationResults);
		}

		$response->setStatus($status);

		return $response->response();

	}

	protected function returnResponse()
	{
		return Response::json($this->response);
	}

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if (!is_null($this->layout)) {
			$this->layout = View::make($this->layout, $this->data);
		}
	}

	public static function action($name)
	{
		if (config('app.debug') && !method_exists(get_called_class(), $name)) {
			$controllerName = get_called_class();
			dd("Controller action '{$name}' does not exist on {$controllerName}");
		}

		return get_called_class().'@'.$name;
	}

}
