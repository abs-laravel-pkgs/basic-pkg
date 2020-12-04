<?php
namespace Abs\BasicPkg\Traits;

use App\Exceptions\ValidationException;
use DB;
use Validator;

trait EloquentValidationTrait {

	public $rules = [];
	public $relationshipRules = [];
	public $validationMessages = [];
	public $fillableRelationships = [];
	public $useSchemaRules = true;
	/**
	 * Contains a list of relationship names that should be validated and
	 * updated/deleted when saving the parent model
	 * @var array
	 */
	public $cascades = [];
	public $errors = [];

	public function setSchemaRules() {
		$tableName = \App::make(get_called_class())->getTable();
		// strip database name from $tableName
		$tableName = substr($tableName, strpos($tableName, '.') + 1);
		$columnRows = \Cache::rememberForever('table-schema-' . $tableName, function () use ($tableName) {
			return DB::select("
					SELECT
						*
					FROM
						information_schema.columns
					WHERE
						table_schema = Database()
						AND table_name = ?
				", [$tableName]);
		});
		foreach ($columnRows as $columnRow) {
			if ($columnRow->COLUMN_NAME == 'original_id') {
				continue;
			}
			if ($columnRow->IS_NULLABLE === 'YES' && !in_array('required', array_get($this->rules, $columnRow->COLUMN_NAME, []))) {
				$this->addRuleIfNotExists($columnRow->COLUMN_NAME, 'nullable');
			}
			if (is_numeric($columnRow->CHARACTER_MAXIMUM_LENGTH)) {
				$this->addRuleIfNotExists($columnRow->COLUMN_NAME, 'string');
				$this->addRuleIfNotExists($columnRow->COLUMN_NAME, 'max:' . $columnRow->CHARACTER_MAXIMUM_LENGTH);
			}
			if (is_numeric($columnRow->NUMERIC_PRECISION)) {
				// just add a boolean rules if column is present in $casts as a bool
				if (array_get($this->casts, $columnRow->COLUMN_NAME) === 'bool' || array_get($this->casts, $columnRow->COLUMN_NAME) === 'boolean') {
					$this->addRuleIfNotExists($columnRow->COLUMN_NAME, 'boolean');
				} else {
					$this->addRuleIfNotExists($columnRow->COLUMN_NAME, 'numeric');
					$unsignedMatch = preg_match('/unsigned$/', $columnRow->COLUMN_TYPE);
					$unsigned = $unsignedMatch === 1;
					$intBounds = [
						'tinyint' => [
							'signed' => [
								'min' => -128,
								'max' => 127,
							],
							'unsigned' => [
								'min' => 0,
								'max' => 255,
							],
						],
						'smallint' => [
							'signed' => [
								'min' => -32768,
								'max' => 32767,
							],
							'unsigned' => [
								'min' => 0,
								'max' => 65535,
							],
						],
						'mediumint' => [
							'signed' => [
								'min' => -8388608,
								'max' => 8388607,
							],
							'unsigned' => [
								'min' => 0,
								'max' => 16777215,
							],
						],
						'int' => [
							'signed' => [
								'min' => -2147483648,
								'max' => 2147483647,
							],
							'unsigned' => [
								'min' => 0,
								'max' => 4294967295,
							],
						],
						'bigint' => [
							'signed' => [
								'min' => -9223372036854775808,
								'max' => 9223372036854775807,
							],
							'unsigned' => [
								'min' => 0,
								'max' => 18446744073709551615,
							],
						],
					];
					switch ($columnRow->DATA_TYPE) {
					case 'tinyint':case 'smallint':case 'mediumint':case 'int':case 'bigint':
						if ($unsigned) {
							$min = $intBounds[$columnRow->DATA_TYPE]['unsigned']['min'];
							$max = $intBounds[$columnRow->DATA_TYPE]['unsigned']['max'];
						} else {
							$min = $intBounds[$columnRow->DATA_TYPE]['signed']['min'];
							$max = $intBounds[$columnRow->DATA_TYPE]['signed']['max'];
						}
						$this->addRuleIfNotExists($columnRow->COLUMN_NAME, "between:$min,$max");
						break;
					case 'decimal':case 'numeric':
						$precision = pow(10, $columnRow->NUMERIC_PRECISION) - 1;
						$scale = $precision / pow(10, $columnRow->NUMERIC_SCALE);
						$max = $scale;
						$min = $unsigned ? 0 : (0 - $max);
						$this->addRuleIfNotExists($columnRow->COLUMN_NAME, "between:$min,$max");
						break;
					case 'float':case 'double':
						break;
					default:
						break;
					}
				}
			}
			if (in_array($columnRow->DATA_TYPE, ['date', 'datetime'])) {
				$this->addRuleIfNotExists($columnRow->COLUMN_NAME, 'date');
			}

		}
	}

	public function addRule($name, $rules, &$rulesArray = null) {
		if ($rulesArray !== null) {
			$rulesArray = &$rulesArray;
		} else {
			$rulesArray = &$this->rules;
		}
		if (is_array($rules)) {
			foreach ($rules as $rule) {
				$rulesArray[$name][] = $rule;
			}
		} elseif (is_string($rules)) {
			$rulesArray[$name][] = $rules;
		}
	}

	public function hasRule($name, $rule, &$rulesArray = null) {
		if ($rulesArray !== null) {
			$rulesArray = &$rulesArray;
		} else {
			$rulesArray = &$this->rules;
		}
		if (isset($rulesArray[$name])) {

			if (is_string($rule)) {
				$ruleName = explode(':', $rule)[0];
				foreach ($rulesArray[$name] as $existingRule) {
					$existingRuleName = explode(':', $existingRule)[0];
					if ($ruleName === $existingRuleName) {
						return true;
					}
				}
			} else {
				throw new Exception('Rule must be a string');
			}
		}
		return false;
	}

	public function addRuleIfNotExists($name, $rule, &$rulesArray = null) {
		if ($rulesArray !== null) {
			$rulesArray = &$rulesArray;
		} else {
			$rulesArray = &$this->rules;
		}
		if (is_string($rule)) {
			if ($this->hasRule($name, $rule, $rulesArray) === false) {
				$this->addRule($name, $rule, $rulesArray);
			}
		} else {
			throw new Exception('Rule must be a string');
		}
	}

	public function removeRules($rulePattern = '/./', &$rulesArray = null) {
		if ($rulesArray !== null) {
			$rulesArray = &$rulesArray;
		} else {
			$rulesArray = &$this->rules;
		}
		foreach ($rulesArray as $name => $rules) {
			$this->removeRule($name, $rulePattern, $rulesArray);
		}
	}

	public function removeRule($name, $rulePattern = '/./', &$rulesArray = null) {
		if ($rulesArray !== null) {
			$rulesArray = &$rulesArray;
		} else {
			$rulesArray = &$this->rules;
		}
		$matches = preg_grep($rulePattern, $rulesArray[$name]);
		$rulesArray[$name] = array_diff($rulesArray[$name], $matches);
		if (empty($rulesArray[$name])) {
			unset($rulesArray[$name]);
		}
	}

	public function validateInput($data) {
		// make a new validator object
		$v = Validator::make($data, $this->getRulesArray());

		// check for failure
		if ($v->fails()) {
			// set errors and return false
			$this->errors = $v->errors();
			return false;
		}

		// validation pass
		return true;
	}

	public function validateInputFiltered(&$data) {
		$this->setSchemaRules();
		$valid = $this->validateInput($data);

		// basic, single-level filter
		foreach ($data as $k => $value) {
			if (array_key_exists($k, $this->rules) == false) {
				unset($data[$k]);
			}
		}

		return $valid;
	}

	public function validateAttrs() {
		if ($this->useSchemaRules) {
			$this->setSchemaRules();
		}

		$messages = $this->validationMessages;

		// make a new validator object
		$v = Validator::make($this->attributes, $this->rules, $messages);

		// check for failure
		if ($v->fails()) {
			//dd($v->errors());
			//Log::debug($v->errors());
			//// set errors and return false
			//if ($this->errors) {
			//	$this->errors = $this->errors->merge($v->errors());
			//}
			//else {
			//	$this->errors = $v->errors();
			//}
			throw new ValidationException($v);
		}

		// validation pass
		return true;
	}

	public function validateRelationships($data) {
		$relationshipRules = $this->relationshipRules;
		$modelKey = $this->getKey() ? $this->getKey() : 0;
		foreach ($relationshipRules as &$itemRules) {
			foreach ($itemRules as &$rule) {
				$rule = str_replace(':modelKey', $modelKey, $rule);
			}
			unset($rule);
		}
		unset($itemRules);

		$messages = $this->validationMessages;
//dd($relationshipRules);
		// make a new validator object
		$v = \Validator::make($data, $relationshipRules, $messages);

		// check for failure
		if ($v->fails()) {
			//// set errors and return false
			//if ($this->errors) {
			//	$this->errors = $this->errors->merge($v->errors());
			//}
			//else {
			//	$this->errors = $v->errors();
			//}
			throw new ValidationException($v);
		}

		// validation pass
		return true;
	}

	public function errors() {
		return $this->errors;
	}

}
