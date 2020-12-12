<?php
namespace Abs\BasicPkg\Traits;

use Abs\BasicPkg\Classes\ApiResponse;
use Abs\BasicPkg\Classes\SaveHelper;
use Abs\BasicPkg\Services\CrudService;
use App\Models\BaseModel;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Request as tRequest;
use Abs\BasicPkg\Classes\InputHelper;

trait CrudTrait {

	/**
	 * Example: index?count=10&page=1&sorting[name]=asc&filter[name]=kevin
	 * @return mixed
	 * @throws Exception
	 */
	public function index() {

		$modelName = $this->model;
		$reflection = new \ReflectionClass($modelName);
		$safeName = $reflection->getShortName();

		$model = new $modelName();
		if (!in_array('index', $model->crudActions)) {
			throw new Exception('Index action is not available on ' . $this->model);
		}
		$qualifiedKeyName = $model->getQualifiedKeyName();

		// Paginate input
		if (Input::get('format') === 'csv') {
			$count = PHP_INT_MAX;
			$offset = 0;
		} else {
			$count = (int) Input::get('count') ? abs(Input::get('count')) : PHP_INT_MAX;
			$offset = (((int) Input::get('page') ? abs(Input::get('page')) : 1) - 1) * $count;
		}

		// Page result
		$query = $modelName::query();
		$totalQuery = clone $query;
		$totalCount = $totalQuery->distinct($qualifiedKeyName)
			->count($qualifiedKeyName);
		CrudService::filterQuery($query);
		$filteredQuery = clone $query;
		$filteredCount = $filteredQuery->distinct($qualifiedKeyName)
			->count($qualifiedKeyName);
		CrudService::sortQuery($query);

		// Using any selects overrides default 'table.*" select
		$query->addSelect($model->getTable() . '.*');
		// Select scopes
		foreach ($model->selects as $select) {
			$scopeName = 'select' . ucfirst(camel_case($select));
			$query->$scopeName();
		}
		$query->take($count)
			->skip($offset); // Paginate results
		$pageResult = $query->groupBy($qualifiedKeyName)
			->get();

		// Relationships
		if (method_exists($modelName, 'relationships')) {
			$pageResult->load($modelName::relationships('index', Input::get('format')));
		}

		// Loading Relationship Counts
		if (method_exists($modelName, 'appendRelationshipCounts')) {
			$pageResult->loadCount($modelName::appendRelationshipCounts('index'), Input::get('format'));
		}



		if (Input::get('format') === 'csv') {
			if ($pageResult->count() === 0) {
				return 'No data to export';
			}
			$exportArr = [];
			foreach ($pageResult as $model) {
				$exportArr[] = $model->toCsvRow();
			}

			$csvObj = new CSV();
			$csvObj->fromArray($exportArr)
				->render('export.csv');

			return true;
		} else {
			$response = new ApiResponse();
			//$response->setData(snake_case($safeName) . '_filtered_count', $filteredCount);
			//$response->setData(snake_case($safeName) . '_total_count', $totalCount);
			//$response->setData(snake_case($safeName) . '_collection', $pageResult);
			$response->setData('filtered_count', $filteredCount);
			$response->setData('total_count', $totalCount);
			$response->setData('collection', $pageResult);
			if (method_exists($this, 'alterCrudResponse')) {
				$this->alterCrudResponse('index', $response);
			}

			return $response->response();
		}
	}

	public function save() {
		return self::_save(new $this->model)->response();
	}

	public function saveFromFormData(Request $request) {
		$modelName = $this->model;
		$result = $modelName::saveFromFormArray($request->all());
		return response()->json($result);
	}

	public function saveFromNgData(Request $request) {
		$modelName = $this->model;
		$result = $modelName::saveFromNgArray($request->all());
		return response()->json($result);
	}

	// public function read($id, $format = 'json') {
	public function read($id, $withtrashed = null) {
		$model = $this->model;
		$format = '';

		if ($withtrashed) {
			$Model = $model::withTrashed()->findOrFail($id);
		} else {
			$Model = $model::findOrFail($id);
		}
		if (!in_array('read', $Model->crudActions)) {
			throw new Exception('Read action is not available on ' . $this->model);
		}

		$response = new ApiResponse();
		if (method_exists($this, 'beforeCrudAction')) {
			$this->beforeCrudAction('read', $response, $Model);
		}
		$modelName = $Model->safeName();
		$modelSnakeName = $Model->snakeName();

		// Relationships
		if (method_exists($modelName, 'relationships')) {
			$Model->load($modelName::relationships('read'));
		}

		// Loading Relationship Counts
		if (method_exists($modelName, 'appendRelationshipCounts')) {
			$Model->loadCount($modelName::appendRelationshipCounts('read'));
		}


		if ($format === 'object') {
			return [
				'success' => true,
				$modelSnakeName => $Model,
			];
		}

		$response->setData($modelSnakeName, $Model);
		if (method_exists($this, 'alterCrudResponse')) {
			$this->alterCrudResponse('read', $response);
		}

		return $response->response();
	}

	public function create() {

		$model = $this->model;
		$Model = new $model();
		if (!in_array('create', $Model->crudActions)) {
			throw new Exception('Create action is not available on ' . $this->model);
		}
		$response = $this->_save($Model);
		if (method_exists($this, 'alterCrudResponse')) {
			$this->alterCrudResponse('create', $response);
		}
		return $response->response();
	}

	public function update($Model) {
		if (!in_array('update', $Model->crudActions)) {
			throw new Exception('Update/save action is not available on ' . $this->model);
		}
		$response = $this->_save($Model);
		if (method_exists($this, 'alterCrudResponse')) {
			$this->alterCrudResponse('update', $response);
		}
		return $response->response();
	}

	public function delete($id, $withtrashed = false) {
		$model = $this->model;
		if ($withtrashed) {
			$Model = $model::withTrashed()->findOrFail($id);
		} else {
			$Model = $model::findOrFail($id);
		}

		if (!in_array('delete', $Model->crudActions)) {
			throw new Exception('Delete action is not available on ' . $this->model);
		}
		$response = new ApiResponse();

		try {
			$Model->checkDelete();
			$Model->delete();
		} catch (Exception $ex) {
			$response->setError($ex);
		}
		if (method_exists($this, 'afterDelete')) {
			$this->afterDelete('delete', $Model);
		}
		if (method_exists($this, 'alterCrudResponse')) {
			$this->alterCrudResponse('delete', $response);
		}
		if (method_exists($this, 'afterDelete')) {
			$this->afterDelete($Model);
		}
		return $response->response();
	}

	private function _save($Model) {
		InputHelper::checkAndReplaceInput();
		if (!in_array('update', $Model->crudActions)) {
			throw new Exception('Update/save action is not available on ' . $this->model);
		}
		$response = new ApiResponse();

		$input = Input::all();
		if (method_exists($this, 'alterCrudInput')) {
			$this->alterCrudInput('save', $input);
		}
		try {
			$controller = $this;
			DB::beginTransaction();
			try {
				if (method_exists($controller, 'beforeSave')) {
					$controller->beforeSave($Model, $input);
				}
				//dd($Model);

				$modelKeyName = $Model->getKeyName();
				$oldKey = array_get($input, $modelKeyName);
				$Model = SaveHelper::uberSave($Model->safeName(), $input);
				$isNew = $oldKey != $Model->$modelKeyName;
				// need to reload the model so that internal attributes array is filled
				$Model = $Model::find($Model->$modelKeyName);
				if (method_exists($controller, 'afterSave')) {
					$controller->afterSave($Model, $isNew, $input, $response);
				}
				// Relationships
				if (method_exists($Model, 'relationships')) {
					$Model->load($Model::relationships('read'));
				}

				// Loading Relationship Counts
				if (method_exists($Model, 'appendRelationshipCounts')) {
					$Model->loadCount($Model::appendRelationshipCounts('read'));
				}

				$modelSnakeName = $Model->snakeName();
				$response->setData($modelSnakeName, $Model);
				if (method_exists($controller, 'alterCrudResponse')) {
					$controller->alterCrudResponse('save', $response);
				}
				DB::commit();
			} catch (Exception $e) {
				DB::rollBack();
				throw $e;
			}
		} catch (Exception $ex) {
			$response->setError($ex);
		}
		return $response;
	}

	public function options() {
		$modelName = $this->model;
		$Model = App::make($modelName);
		if (!in_array('options', $Model->crudActions)) {
			throw new Exception('Options action is not available on ' . $modelName);
		}
		// $ModelResult = clone $Model;
		// if (method_exists($Model, 'scopeOrderDefault')) {
		// 	$ModelResult = $ModelResult->orderDefault();
		// }

		$query = $modelName::query();
		$filter = tRequest::input('filter', []);
		if (isset($modelName::$HAS_COMPANY) && $modelName::$HAS_COMPANY) {
			$filter['company'] = true;
		}

		CrudService::filterQuery($query, $filter);
		$filteredQuery = clone $query;
		CrudService::sortQuery($query);

		$ModelResult = clone $Model;
		if (method_exists($Model, 'scopeOrderDefault')) {
			$query = $query->orderDefault();
		}

		if (method_exists($Model, 'selectableFields')) {
			$query = $query->select($modelName::selectableFields('options'));
		}
		$ModelResult = $query->get();

		// Loading Relationships
		if (method_exists($modelName, 'relationships')) {
			$ModelResult->load($modelName::relationships('options'));
		}

		// Loading Relationship Counts
		if (method_exists($modelName, 'appendRelationshipCounts')) {
			$ModelResult->loadCount($modelName::appendRelationshipCounts('options'));
		}

		$response = new ApiResponse();
		$response->setData('options', $ModelResult);
		if (method_exists($this, 'alterCrudResponse')) {
			$this->alterCrudResponse('options', $response);
		}
		return $response->response();
	}

	//Event Callback function
	/**
	 * Presents an opportunity to modify the contents of the input before running crud action
	 * @param $action currently only works for save
	 * @param $input
	 */
	public function alterCrudInput($action, &$input) {
		// DO NOT PLACE CODE IN HERE, THIS IS FOR DOCUMENTATION PURPOSES ONLY
	}

	/**
	 * Presents an opportunity to run code before the crud action has taken place
	 * @param $action currently only works for save
	 * @param ApiResponse $response
	 * @param BaseModel $Model
	 */
	public function beforeCrudAction($action, ApiResponse $Response, $Model) {
		// DO NOT PLACE CODE IN HERE, THIS IS FOR DOCUMENTATION PURPOSES ONLY
	}

	/**
	 * Presents an opportunity to modify the contents of the ApiResponse before crud action completes
	 * @param string $action = index|create|read|update|delete|options
	 * @param ApiResponse $response
	 */
	public function alterCrudResponse($action, ApiResponse $Response) {
		// DO NOT PLACE CODE IN HERE, THIS IS FOR DOCUMENTATION PURPOSES ONLY
	}

	/**
	 * Presents an opportunity to modify the object before saving it to DB
	 *
	 * @param $Model
	 * @param $input
	 */
	public function beforeSave($Model, $input){
		// DO NOT PLACE CODE IN HERE, THIS IS FOR DOCUMENTATION PURPOSES ONLY
	}

	/**
	 * Presents an opportunity to modify the object after saving it to DB
	 *
	 * @param $Model
	 * @param $isNew
	 * @param $input
	 * @param  ApiResponse  $response
	 */
	public function afterSave($Model, $isNew, $input, ApiResponse $response){
		// DO NOT PLACE CODE IN HERE, THIS IS FOR DOCUMENTATION PURPOSES ONLY
	}
}
