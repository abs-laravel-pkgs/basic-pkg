<?php

namespace Abs\BasicPkg\Services;

use Abs\BasicPkg\Helpers\AbsStringHelper;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Request;
use App\Exceptions\ValidationException;
use Validator;
use Abs\BasicPkg\Helpers\AbsStringHelper as StringHelper;

class CrudService {

	/**
	 * Get the standard validation rules for an index route
	 *
	 * @return array
	 */
	public static function getIndexValidationRules() {
		return [
			'filter' => [
				'array',
			],
			'sorting' => [
				'array',
			],
			'page' => [
				'numeric',
				'min:1',
			],
			'count' => [
				'numeric',
				'min:1',
			],
		];
	}

	/**
	 * Validate input with given $data and $rules, throw a App\Exceptions\ValidationException if validation fails
	 *
	 * @param array|null $data
	 * @param array|null $rules
	 * @throws ValidationException
	 */
	public static function validateInput(array $data = null, array $rules = null) {
		$v = Validator::make($data, $rules);
		if ($v->fails()) {
			throw new ValidationException($v);
		}
	}

	/**
	 * Validate index input using standard index validation rules
	 *
	 * @param array|null $rules
	 * @throws ValidationException
	 */
	public static function validateIndexInput(array $rules = null) {
		$data = Request::input();
		$rules = is_array($rules) ? $rules : self::getIndexValidationRules();
		self::validateInput($data, $rules);
	}

	/**
	 * Filter a query builder by its scopes.
	 *
	 * @param Builder $query
	 * @param array $filter
	 * @throws Exception If matching scope not found.
	 */
	public static function filterQuery(Builder $query, array $filter = null) {
		if (!is_array($filter)) {
			$filter = Request::input('filter', []);
		}

		$model = $query->getModel();
		foreach ($filter as $k => $v) {
			$methodName = 'scopeFilter' . StringHelper::upper_camel_case($k);
			if (method_exists($model, $methodName)) {
				$scopeName = 'filter' . StringHelper::upper_camel_case($k);
				$filterValue = $v;
				// convert into boolean if necessary
				$filterValue = $filterValue === 'true' ? true : $filterValue;
				$filterValue = $filterValue === 'false' ? false : $filterValue;
				$query->$scopeName($filterValue);
			}
			else {
				throw new Exception('Filter scope "scopeFilter' . StringHelper::upper_camel_case($k) . '" not defined');
			}
		}
	}

	public static function sortQuery(Builder $query, $order = null) {
		// TODO: Figure how to check for columns added through withCount() or selectSub()
		$orderArray = [];
		if (is_null($order)) {
			$order = Request::input('order');
		}

		if (is_array($order)) {
			$orderArray = $order;
		}
		else if (is_string($order)) {
			$orderArray[] = $order;
		}

		if (count($orderArray)) {
			$model = $query->getModel();
			$table = $model->getTable();
			$columns = $model->getSchemaColumns();

			foreach ($orderArray as $order) {
				$dir = 'asc';
				if (strpos($order, '-') === 0) {
					$dir = 'desc';
					$order = substr($order, 1);
				}
				$scopeMethod = 'orderBy' . AbsStringHelper::upper_camel_case($order);
				if (method_exists($model, 'scope' . $scopeMethod)) {
					$query->$scopeMethod($dir);
				}
				else if (in_array($order, $columns)) {
					$query->orderByRaw("ISNULL(?) " . $dir, [
						"{$table}.{$order}",
						//$dir,
					]);
					$query->orderBy("{$table}.{$order}", $dir);
				}
			}
		}
	}

	public static function paginateQuery(Builder $query, $page = null, $count = null) {
		if (!is_numeric($page)) {
			$page = Request::input('page', 1);
		}

		if (!is_numeric($count)) {
			$count = Request::input('count', 10);
		}

		$offset = ($page - 1) * $count;
		$query->skip($offset);
		$query->take($count);
	}

	/**
	 * Get a count of distinct primary keys.
	 *
	 * @param Builder $query
	 * @return mixed
	 */
	public static function distinctCount(Builder $query) {
		$model = $query->getModel();
		$keyName = $model->getQualifiedKeyName();
		return $query->count(DB::raw('DISTINCT ' . $keyName));
	}

}
