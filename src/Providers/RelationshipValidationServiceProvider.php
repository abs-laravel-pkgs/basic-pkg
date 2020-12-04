<?php

namespace Abs\BasicPkg\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class RelationshipValidationServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		Validator::extend('belongsTo', function ($attribute, $value, $parameters) {
			if ($value === false || empty($value)) {
				// value has been set to false so that it will be disocciated
				return true;
			}

			$value = is_object($value) ? (array) $value : $value;

			if (is_callable($parameters[1])) {
				$userFuncParameters = array_slice($parameters, 2);
				$validObjectIds = call_user_func_array($parameters[1], $userFuncParameters)->toArray();
			}
			else if (is_string($parameters[1])) {
				$validObjectIds = explode(',', $parameters[1]);
			}
			else {
				return false;
			}

			$modelName = $parameters[0];
			$modelKeyName = \App::make($modelName)
								->getKeyName()
			;

			if (is_array($value) && in_array($value[$modelKeyName], $validObjectIds)) {
				return true;
			}
			else {
				return false;
			}
		});

		Validator::replacer('belongsTo', function ($message, $attribute, $rule, $parameters) {
			return str_replace(array(':attribute'), array($attribute), $message);
		});

		Validator::extend('belongsToMany', function ($attribute, $value, $parameters) {
			if (is_null($value)) {
				return true;
			}

			if (is_callable($parameters[1])) {
				$userFuncParameters = array_slice($parameters, 2);
				$validObjectIds = call_user_func_array($parameters[1], $userFuncParameters)->toArray();
			}
			else if (is_string($parameters[1])) {
				$validObjectIds = explode(',', $parameters[1]);
			}
			else {
				return false;
			}

			$value = is_object($value) ? (array) $value : $value;

			$modelName = $parameters[0];
			$modelKeyName = \App::make($modelName)
								->getKeyName()
			;

			$inputObjectIds = [];
			foreach ($value as $valueItem) {
				$valueItem = (array) $valueItem;
				if (empty($valueItem[$modelKeyName])) {
					return false;
				}
				$inputObjectIds[] = $valueItem[$modelKeyName];
			}

			$invalidIdCount = count(array_diff($inputObjectIds, $validObjectIds));
			if ($invalidIdCount) {
				return false;
			}

			return true;
		});

		Validator::replacer('belongsToMany', function ($message, $attribute, $rule, $parameters) {
			return str_replace(array(':attribute'), array($attribute), $message);
		});
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}

}
