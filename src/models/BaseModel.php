<?php

namespace Abs\BasicPkg;

use Abs\BasicPkg\Traits\EloquentValidationTrait;
use App\Exceptions\ValidationException;
use Auth;
use Cache;
use Carbon\Carbon;
use Config;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LocaleHelper;
use ReflectionClass;
use Request;
use Validator;

abstract class BaseModel extends Model {
	use EloquentValidationTrait;

	// Getter & Setters --------------------------------------------------------------

	// Relations --------------------------------------------------------------

	// Dynamic Attributes --------------------------------------------------------------

	// Query Scopes --------------------------------------------------------------

	// Static Operations --------------------------------------------------------------

	public static $AUTO_GENERATE_CODE = false;

	protected static $excelColumnRules = [
		'Code' => [
			'table_column_name' => 'code',
			'rules' => [
				'required' => [
				],
			],
		],
		'Name' => [
			'table_column_name' => 'name',
			'rules' => [
				'required' => [
				],
			],
		],
	];


	protected $fillable = [
		"company_id",
		"code",
		"name",
	];

	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	protected $casts = [
	];

	// Getter & Setters --------------------------------------------------------------

	// Relations --------------------------------------------------------------

	public function company() {
		return $this->belongsTo('App\Company');
	}

	public function createdBy() {
		return $this->belongsTo('App\User', 'created_by_id');
	}

	public function updatedBy() {
		return $this->belongsTo('App\User', 'updated_by_id');
	}

	public function deletedBy() {
		return $this->belongsTo('App\User', 'deleted_by_id');
	}

	public $timestamps = false;
	public $autovalidate = true;
	public $validationRules = [];
	public $setsReferences = false;

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);

		$database = $this->getConnection()
			->getConfig('database');
		if (!strpos($this->getTable(), '.')) {
			$this->table = "{$database}.{$this->getTable()}";
		}
		$this->appends[] = 'class';
	}

	public static function boot() {

		parent::boot();

		static::saving(function (self $Model) {
			// automatic validation before saving
			if (Config::get('app.autovalidate')) {
				if ($Model->autovalidate) {
					try {
						$Model->validateAttrs();
					} catch (Exception $ex) {
						throw $ex;
					}
				}
			}
		});
	}

	// Attributes --------------------------------------------------------------

	// Query Scopes --------------------------------------------------------------

	public function scopeFilterSearch($query, $term) {
		if (strlen($term)) {
			$query->where(function ($query) use ($term) {
				$query->orWhere('code', 'LIKE', '%' . $term . '%');
				$query->orWhere('name', 'LIKE', '%' . $term . '%');
			});
		}
	}

	public function scopeCompany($query, $table_name = null) {
		// if ($table_name) {
		// 	$table_name .= '.';
		// } else {
		// 	$table_name = '';
		// }
		// return $query->where($table_name . 'company_id', Auth::user()->company_id);
		return $query->where($this->table . '.company_id', Auth::user()->company_id);

	}

	// Static Operations --------------------------------------------------------------

	public static function keyName() {
		return (new static )->getKeyName();
	}

	public static function table() {
		return (new static )->getTable();
	}

	public function getQualifiedColumnName($column) {
		return $this->getTable() . '.' . $column;
	}

	public static function qualifiedColumnName($column) {
		return (new static )->getQualifiedColumnName($column);
	}

	public static function qualifiedKeyName() {
		return (new static )->getQualifiedKeyName();
	}

	public static function supportsSoftDeletes() {
		return in_array(SoftDeletes::class, class_uses(new static ));
	}

	public function getFullClassAttribute() {
		return get_class($this);
	}

	public function hasAttribute($attr) {
		return array_key_exists($attr, $this->attributes);
	}

	public function getClassAttribute() {
		return $this->safeName();
		//return get_called_class();
	}

	public function getNitroClass() {
		return array_get(config('nitro.morphMap'), get_class($this), 'UNDEFINED');
	}

	public function getNitroClassAttribute() {
		return $this->getNitroClass();
	}

	public function getDeletedAttribute() {
		return $this->attributes['deleted_at'] != null;
	}

	public function setDeletedAttribute($value) {
		if ($value) {
			$this->deleted_at = Carbon::now();
		} else {
			$this->deleted_at = null;
		}
	}

	public function fillDateAttribute($name, $value) {
		array_set($this->attributes, $name, $value ? Carbon::parse($value) : null);
	}

	public function fillTimeAttribute($name, $value) {
		array_set($this->attributes, $name, $value ? Carbon::parse($value)->format('H:i:s') : null);
	}

	/**
	 * Fill a date attribute as a string to prevent false-positive dirty checks
	 *
	 * @param $name
	 * @param $value
	 */
	public function cleanFillDateAttribute($name, $value) {
		$attributeValue = null;
		if ($value) {
			$parsedValue = Carbon::parse($value);
			$attributeValue = $parsedValue->toDateString();
		}
		array_set($this->attributes, $name, $attributeValue);
	}

	/**
	 * Fill a time attribute as a string to prevent false-positive dirty checks
	 *
	 * @param $name
	 * @param $value
	 */
	public function cleanFillTimeAttribute($name, $value) {
		$attributeValue = null;
		if ($value) {
			$parsedValue = Carbon::parse($value);
			$attributeValue = $parsedValue->toTimeString();
		}
		array_set($this->attributes, $name, $attributeValue);
	}

	/**
	 * Fill a datetime attribute as a string to prevent false-positive dirty checks
	 *
	 * @param $name
	 * @param $value
	 */
	public function cleanFillDateTimeAttribute($name, $value) {
		$attributeValue = null;
		if ($value) {
			$parsedValue = Carbon::parse($value);
			$attributeValue = $parsedValue->toDateTimeString();
		}
		array_set($this->attributes, $name, $attributeValue);
	}

	private static function processLocale(&$obj) {
		// Date locale
		foreach ((array) $obj->localeDates as $date) {
			if (!$obj->{$date}) {
				$obj->{$date} = null;
			} else {
				$obj->{$date} = LocaleHelper::unformatDate($obj->{$date});
			}
		}
	}

	public function unsetRelations() {
		$this->relations = [];
	}

	private static function processUserTracking(&$obj) {
		if (isset($obj->userTracking) && $obj->userTracking === true) {
			if (!isset($obj->created_by) || !$obj->created_by) {
				$obj->created_by = Auth::check() ? Auth::user()->id : 0;
			}
			$obj->updated_by = Auth::check() ? Auth::user()->id : 0;
		}
	}

	/**
	 * CRUD API
	 */
	public $allowCrud = false;
	public $crudActions = ['index', 'create', 'save', 'read', 'update', 'delete', 'options'];
	// names of select scopes to be run when loading (currently only INDEX)
	public $selects = [];

	public function shortSafeName() {
		$reflect = new ReflectionClass(get_called_class());
		return $reflect->getShortName();
	}

	public function safeName() {
		return get_called_class();
	}

	public function snakeName() {
		return snake_case($this->shortSafeName());
	}

	/*
		 * VISIBILITY
	*/

	public function getVisible() {
		return $this->visible;
	}

	//public function addVisible(array $visible) {
	//	$this->setVisible(array_merge($this->getVisible(), $visible));
	//}

	public function removeVisible(array $visible) {
		foreach ($visible as $remove) {
			$arrayKeys = array_keys($this->visible, $remove);
			foreach ($arrayKeys as $arrayKey) {
				unset($this->visible[$arrayKey]);
			}
		}
	}

	//public function addHidden(array $hidden) {
	//	$this->setHidden(array_merge($this->getHidden(), $hidden));
	//}

	public function removeHidden(array $hidden) {
		foreach ($hidden as $remove) {
			$arrayKeys = array_keys($this->hidden, $remove);
			foreach ($arrayKeys as $arrayKey) {
				unset($this->hidden[$arrayKey]);
			}
		}
	}

	public function addAppends(array $appends) {
		$this->appends = array_merge($this->appends, $appends);
	}

	public function removeAppends(array $appends) {
		$this->appends = array_diff($this->appends, $appends);
	}

	/**
	 * Extend this method to throw exception if the record is not deletable
	 * @throws Exception if model should not be deleted
	 */

	public function fillRelationships($data, $relationTypes = ['belongsTo'], $disassociateMissing = false) {
		// TODO: Replace references to 'id' column with getKeyName()
		/* $data format
		  $data = [
		  'belongsToFunctionName' => [
		  'id' => 1
		  ],
		  'belongsToManyFunctionName' => [
		  [
		  'id' => 1
		  ],
		  [
		  'id' => 2
		  ],
		  [
		  'id' => 5
		  ],
		  ],
		  ];
		 */

		foreach ($data as $key => $value) {

			if (is_array($value) == false) {
				continue;
			}

			$relation = camel_case($key);

			if (in_array($relation, $this->fillableRelationships)) {
				$relationship = $this->$relation();

				switch (get_class($relationship)) {
				case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
					if (in_array('belongsTo', $relationTypes)) {

						// make sure $value is an array
						$value = (array) $value;
						// load related model
						$relatedClass = get_class(($this->$relation()->getRelated()));
						$Related = $relatedClass::find($value['id']);

						// associate parent model
						$this->$relation()->associate($Related);
					}
					break;
				case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
					if (in_array('belongsToMany', $relationTypes)) {
						$idArray = [];
						foreach ($value as $valueItem) {
							// make sure $valueItem is an array
							$valueItem = (array) $valueItem;
							$idArray[] = $valueItem['id'];
						}
						$this->$relation()->sync($idArray);
					}
					break;
				default:
					break;
				}
			}

		}

		if ($disassociateMissing) {
			$camelCaseKeys = [];
			foreach ($data as $key => $value) {
				$camelCaseKeys[camel_case($key)] = $value;
			}

			foreach ($this->fillableRelationships as $fillableRelationship) {
				// disassociate if not in data array or otherwise empty
				if (empty($camelCaseKeys[$fillableRelationship])) {
					$relationship = $this->$fillableRelationship();

					switch (get_class($relationship)) {
					case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
						if (in_array('belongsTo', $relationTypes)) {
							// disassociate parent model
							$foreignKey = $relationship->getForeignKey();
							$this->$foreignKey = 0;
						}
						break;
					case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
						if (in_array('belongsToMany', $relationTypes)) {
							// detach all related records
							$idArray = [];
							$this->$relation()->sync($idArray);
						}
						break;
					default:
						break;
					}
				}
			}
		}
	}

	public function checkDelete() {
		// DO NOT PLACE CODE IN HERE, THIS IS FOR DOCUMENTATION PURPOSES ONLY
		//if ($canDelete !== true) {
		//	throw new Exception('Model can not be deleted');
		//}
	}

	public static function optionIds($string = false) {
		$class = get_called_class();
		$Model = new $class;
		$lists = $Model;
		$lists = $lists->get()->pluck($Model->getKeyName());
		if ($string) {
			return implode(',', $lists->toArray());
		} else {
			return $lists;
		}
	}

	// Query Scopes ------------------------------------------------------------

	public function scopeSelectTable(Builder $q): Builder{
		$q->addSelect($this->getTable() . '.*');

		return $q;
	}

	public function scopeWhereKey($q, $key) {
		$q->where(self::qualifiedKeyName(), '=', $key);
	}

	public function scopeWhereKeyIn($q, $keyArray) {
		$q->whereIn(self::qualifiedKeyName(), $keyArray);
	}

	public function scopeWhereId($q, $id) {
		$q->whereKey($id);
	}

	public function scopeWhereIdIn($q, $idArray) {
		$q->whereKeyIn($idArray);
	}

	public function scopeWhereOriginalId($q, $originalId) {
		//$q->withoutCurrentFranchise();
		if (in_array(SoftDeletes::class, class_uses($this))) {
			$q->withTrashed();
		}
		$q->where($this->getTable() . '.original_id', '=', $originalId);
	}

	public function getSchema() {
		$connection = $this->getConnection();
		$connectionName = $connection->getName();
		$databaseName = $connection->getDatabaseName();
		$tableName = $this->getTable();
		// strip database name from $tableName
		$tableName = substr($tableName, strpos($tableName, '.') + 1);
		$schemaRows = Cache::rememberForever("db.{$connectionName}.{$databaseName}.{$tableName}.schema", function () use ($connectionName, $databaseName, $tableName) {
			return DB::connection($connectionName)
				->select("
						SELECT
							*
						FROM
							information_schema.columns
						WHERE
							table_schema = ?
							AND table_name = ?
					 ", [
					$databaseName,
					$tableName,
				])
			;
		});
		return $schemaRows;
	}

	public function getSchemaColumns() {
		$columns = [];
		foreach ($this->getSchema() as $row) {
			$columns[] = $row->COLUMN_NAME;
		}
		return $columns;
	}

	// Validation

	public function getSchemaRules() {
		$columnRows = $this->getSchema();
		$rules = [];
		foreach ($columnRows as $columnRow) {
			$columnName = $columnRow->COLUMN_NAME;

			if ($columnRow->IS_NULLABLE == 'YES') {
				$rules[$columnName][] = 'nullable';
			}
			if (is_numeric($columnRow->CHARACTER_MAXIMUM_LENGTH)) {
				$rules[$columnName][] = 'string';
				$rules[$columnName][] = 'max:' . $columnRow->CHARACTER_MAXIMUM_LENGTH;
			}
			if (is_numeric($columnRow->NUMERIC_PRECISION)) {
				// just add a boolean rules if column is present in $casts as a bool
				if (array_get($this->casts, $columnName) === 'bool') {
					$rules[$columnName][] = 'boolean';
				} else {
					$rules[$columnName][] = 'numeric';
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
					case 'tinyint':
					case 'smallint':
					case 'mediumint':
					case 'int':
					case 'bigint':
						if ($unsigned) {
							$min = $intBounds[$columnRow->DATA_TYPE]['unsigned']['min'];
							$max = $intBounds[$columnRow->DATA_TYPE]['unsigned']['max'];
						} else {
							$min = $intBounds[$columnRow->DATA_TYPE]['signed']['min'];
							$max = $intBounds[$columnRow->DATA_TYPE]['signed']['max'];
						}
						$rules[$columnName][] = "between:$min,$max";
						break;
					case 'decimal':
					case 'numeric':
						$precision = pow(10, $columnRow->NUMERIC_PRECISION) - 1;
						$scale = $precision / pow(10, $columnRow->NUMERIC_SCALE);
						$max = $scale;
						$min = $unsigned ? 0 : (0 - $max);
						$rules[$columnName][] = "between:$min,$max";
						break;
					case 'float':
					case 'double':
						break;
					default:
						break;
					}
				}
			}
			if (in_array($columnRow->DATA_TYPE, [
				'date',
				'datetime',
			])) {
				$rules[$columnName][] = 'date';
			}

		}
		return $rules;
	}

	public function getRules() {
		return array_merge_recursive($this->getSchemaRules(), $this->validationRules);
	}

	public function validFill($input = null) {
		if (is_null($input)) {
			$input = Request::all();
		}
		if (!$this->totallyGuarded()) {
			$this->fill($input);
		}
		$v = Validator::make($this->attributes, $this->getRules());
		\Log::info($this->getRules());
		if ($v->fails()) {
			throw new ValidationException($v);
		}
		return $this;
	}

	public function scopeNot($q, $keyOrInstance) {
		$key = $keyOrInstance instanceof self ? $keyOrInstance->getKey() : $keyOrInstance;
		$q->where(self::getQualifiedKeyName(), '!=', $key);
	}

	public function scopeSelectBaseTable($q) {
		$q->addSelect(self::table() . '.*');
	}

	public function scopeWithoutBaseTable($q) {
		$q->withoutGlobalScope('selectBaseTable');
	}

	public function scopeWithoutCurrentFranchise(Builder $q) {
		$q->withoutGlobalScope('currentFranchise');
	}

	public function getModelNameAttribute() {
		return get_called_class();
	}

	public function getShortModelNameAttribute() {
		return (new ReflectionClass(get_called_class()))->getShortName();
	}

}
