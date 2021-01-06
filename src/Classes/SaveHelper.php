<?php
namespace Abs\BasicPkg\Classes;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaveHelper {

	const CASCADE_NONE = 0;
	const CASCADE_UNLINK = 1;
	const CASCADE_DELETE = 2;

	public static function uberSave($modelName, $input) {
		$modelKeyName = App::make($modelName)->getKeyName();
		$Model = null;
		DB::transaction(function () use ($modelName, $modelKeyName, $input, &$Model) {
			if (isset($input[$modelKeyName]) && $input[$modelKeyName]) {
				$Model = $modelName::find($input[$modelKeyName]);
			} else {
				$Model = new $modelName();
			}

			self::saveModel($Model, $input);
		});
		return $Model;
	}

	public static function deleteModel(BaseModel $Model) {
		if ($Model->exists) {
			self::deleteRelations($Model);
			$Model->delete();
		}
	}

	public static function saveModel(BaseModel $Model, & $input) {
		if ($Model->exists && Arr::get($input, 'delete')) {
			self::deleteModel($Model);
		}
		else if (!Arr::get($input, 'delete')) {
			$Model->fill($input);
			$Model->validateAttrs();
			$Model->validateRelationships($input);
			if(!Arr::get($input, 'company.id')){
				if($Model::hasCompany()){
					$Model->company_id = Auth::user()->company_id;
				}
			}

			self::saveRelations($Model, $input);
			$Model->save();
			$modelKeyName = $Model->getKeyName();
			$input[$modelKeyName] = $Model->$modelKeyName;
		}
	}

	/**
	 * Save a model's parent relations
	 *
	 * Only the $Model will be saved. Parents will be unaffected. If an exception is thrown all changes will be rolled
	 * back.
	 *
	 * @param BaseModel  $Model The model to be saved
	 * @param array $input The input to be validated and stored
	 * @throws Exception if invalid fillable relationship found
	 */
	public static function saveRelations(BaseModel $Model, &$input) {
		// need to fill BelongsTo relations before saving
		self::relationIterator($Model, $input, false);
		// save so that we can be BelongsToMany and HasOneOrMany relations
		$Model->save();
		$modelKeyName = $Model->getKeyName();
		$input[$modelKeyName] = $Model->$modelKeyName;
		self::relationIterator($Model, $input, true);
		//dd(1);
	}


	private static function relationIterator(BaseModel $Model, &$input, $afterSave = false) {
		//dump($Model->fillableRelationships);

		// Saving belongsTo relations
		// do all belongsTo relations before any saving as they're likely to have foreign key constraints
		foreach ($Model->fillableRelationships as $k => $v) {
			if (is_string($k)) {
				// Associative array, take the key-value pairs literally
				$relationInputName = $k;
				$relationMethodName = $v;
			} else {
				// Indexed array, generate both names from value
				$relationInputName = Str::snake($v);
				$relationMethodName = Str::camel($v);
			}
			$Relation = $Model->$relationMethodName();
			if (array_key_exists($relationInputName, $input)) {
				// if the input is present...
				$relationInput = isset($input[$relationInputName]) ? $input[$relationInputName] : false;
				//dump($v, $relationInput);
			} else {
				// else, if input not present, will be skipped
				$relationInput = null;
			}
			// if the input is present and we know how to save the relationship type, save it
			if (isset($relationInput) && is_a($Relation, BelongsTo::class) && $afterSave === false) {
				self::saveBelongsToRelation($Model, $Relation, $relationInput);
				$input[$relationInputName] = $relationInput;
			}
			// if the input is present and we know how to save the relationship type, save it
			if (isset($relationInput) && is_a($Relation, MorphTo::class) && $afterSave === false) {
				self::saveMorphToRelation($Model, $Relation, $relationInput);
				$input[$relationInputName] = $relationInput;
			}
			// if the input is present and we know how to save the relationship type, save it
			if(isset($relationInput)) {
				if (is_a($Relation, BelongsToMany::class) && $afterSave === true) {
					self::saveBelongsToManyRelation($Model, $Relation, $relationInput);
					$input[$relationInputName] = $relationInput;
				}
				else if (is_a($Relation, HasOneOrMany::class) && $afterSave === true) {
					self::saveHasOneOrManyRelation($Model, $Relation, $relationInput);
					$input[$relationInputName] = $relationInput;
				}
			}


			//if (isset($relationInput) && is_a($Relation, 'Illuminate\Database\Eloquent\Relations\BelongsToMany') && $afterSave === true) {
			//	self::saveBelongsToManyRelation($Model, $Relation, $relationInput);
			//	$input[$relationInputName] = $relationInput;
			//} else if (isset($relationInput) && is_a($Relation, 'Illuminate\Database\Eloquent\Relations\HasOneOrMany') && $afterSave === true) {
			//	self::saveHasOneOrManyRelation($Model, $Relation, $relationInput);
			//	$input[$relationInputName] = $relationInput;
			//}

		}

		// Saving belongsToMany and hasOne and hasMany relations
		//foreach ($Model->fillableRelationships as $k => $v) {
		//	if (is_string($k)) {
		//		// Associative array, take the key-value pairs literally
		//		$relationInputName = $k;
		//		$relationMethodName = $v;
		//	}
		//	else {
		//		// Indexed array, generate both names from value
		//		$relationInputName = snake_case($v);
		//		$relationMethodName = camel_case($v);
		//	}
		//	$Relation = $Model->$relationMethodName();
		//	if (array_key_exists($relationInputName, $input)) { // if the input is present...
		//		$relationInput = $input[$relationInputName] ?? false;
		//	}
		//	else { // else, if input not present, will be skipped
		//		$relationInput = null;
		//	}
		//	// if the input is present and we know how to save the relationship type, save it
		//	if(isset($relationInput)) {
		//		if (is_a($Relation, BelongsToMany::class) && $afterSave === true) {
		//			self::saveBelongsToManyRelation($Model, $Relation, $relationInput);
		//			$input[$relationInputName] = $relationInput;
		//		}
		//		else if (is_a($Relation, HasOneOrMany::class) && $afterSave === true) {
		//			self::saveHasOneOrManyRelation($Model, $Relation, $relationInput);
		//			$input[$relationInputName] = $relationInput;
		//		}
		//	}
		//}
	}

	/**
	 * Save $Model's belongsTo relationships
	 *
	 * Only touches $Model, parent models will be unaffected.
	 *
	 * @param  BaseModel  $Model
	 * @param  BelongsTo  $Relation
	 * @param  mixed  $relationInput  array = save relation; false = remove relation
	 */
	public static function saveBelongsToRelation(BaseModel $Model, BelongsTo $Relation, & $relationInput): void
	{
		$foreignKey = $Relation->getForeignKeyName();
		$otherKeyName = $Relation->getOwnerKeyName();
		if (is_array($relationInput)) {
			if (Arr::get($relationInput, $otherKeyName)) {
				$otherKeyValue = Arr::get($relationInput, $otherKeyName);
				//dump($relationInput, $foreignKey, $otherKeyName, $otherKeyValue);
				// related model should be validated already, no need to reload from DB by id
				$Relation->associate($otherKeyValue);
			} else {
				// manually set key since dissociate makes it null
				$Relation->dissociate();
//				$Model->setAttribute($foreignKey, 0);
			}
		} else if ($relationInput === false) {
			// manually set key since dissociate makes it null
			$Relation->dissociate();
//			$Model->setAttribute($foreignKey, 0);
		}
		//$Model->save();
	}

	/**
	 * Save $Model's morphTo relationships
	 *
	 * Only touches $Model, parent models will be unaffected.
	 *
	 * @param  BaseModel  $Model
	 * @param  BelongsTo  $Relation
	 * @param  mixed  $relationInput  array = save relation; false = remove relation
	 */
	public static function saveMorphToRelation(BaseModel $Model, MorphTo $Relation, & $relationInput): void
	{
		$foreignKey = $Relation->getForeignKeyName();
		$morphType = $Relation->getMorphType();
		//dd($foreignKey, $morphType);
		if (is_array($relationInput)) {
			if (Arr::get($relationInput, 'id')) {
				$otherKeyValue = Arr::get($relationInput, 'id');
				$Model->$morphType = Arr::get($relationInput, 'class');
				$Model->$foreignKey = $otherKeyValue;
				//dump($relationInput, $foreignKey, $otherKeyName, $otherKeyValue);
				// related model should be validated already, no need to reload from DB by id
				//$Relation->associate($otherKeyValue);
			} else {
				// manually set key since dissociate makes it null
				$Relation->dissociate();
//				$Model->setAttribute($foreignKey, 0);
			}
		} else if ($relationInput === false) {
			// manually set key since dissociate makes it null
			$Relation->dissociate();
//			$Model->setAttribute($foreignKey, 0);
		}
		//$Model->save();
	}

	/**
	 * Save $Model's belongsToMany relationships
	 *
	 * Only touches $Model, parent models will be unaffected
	 *
	 * @param  BaseModel  $Model
	 * @param  BelongsToMany  $Relation
	 * @param  mixed  $relationInput  array = save relation; false = remove relation
	 */
	public static function saveBelongsToManyRelation(BaseModel $Model, BelongsToMany $Relation, & $relationInput): void {
		if (is_array($relationInput) || $relationInput === false) { // if we have an array of related models or just want to clear all
			$syncIds = [];
			if (is_array($relationInput)) {
				$RelationRelated = $Relation->getRelated();
				//$relatedClass = get_class($RelationRelated);
				$relatedModelKeyName = $RelationRelated->getKeyName(); // Primary key column name
				foreach ($relationInput as $inputItem) {
					if (isset($inputItem[$relatedModelKeyName]) && $inputItem[$relatedModelKeyName]) {
						$syncIds[] = $inputItem[$relatedModelKeyName];
					}
				}
			}
			if ($Model->exists !== true) {
				$Model->save();
			}
			$Relation->sync($syncIds);
		} else {
			throw new Exception('Invalid $relationInput type');
		}
	}

	/**
	 * Save $Model's hasOneOrMany relationships
	 *
	 * Only touches $Model's descendants
	 *
	 * @param  BaseModel  $Model
	 * @param  HasOneOrMany  $Relation
	 * @param  mixed  $relationInput  array = save relation; false = remove relation
	 */
	public static function saveHasOneOrManyRelation(BaseModel $Model, HasOneOrMany $Relation, & $relationInput): void
	{
		// TODO: DELETE EMPTY ARRAY/FALSE RELATIONS
		if ($Relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne) {
			self::saveHasRelation($Model, $Relation, $relationInput);
		} else if ($Relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
			foreach ($relationInput as &$relationInputItem) {
				self::saveHasRelation($Model, $Relation, $relationInputItem);
			}
			unset($relationInputItem);
		} else if ($Relation instanceof \Illuminate\Database\Eloquent\Relations\MorphOne) {
			self::saveMorphOneRelation($Model, $Relation, $relationInput);
		}
	}

	/**
	 * saveHasOneOrManyRelation helper function
	 *
	 * @param  BaseModel  $Model
	 * @param  MorphOne  $Relation
	 * @param  array  $relationInput
	 */
	private static function saveMorphOneRelation(BaseModel $Model, MorphOne $Relation, & $relationInput): void
	{
		if ($Model->exists !== true) {
			$Model->save();
		}
		// insert parent model into the input so that it can fill the related model
		$Model->unsetRelations(); // Temporary(?) fix as objects up the chain won't have attributes necessary for any virtual attributes they employ, thus breaking toArray()
		$relationInput[$Model->snakeName()] = $Model->toArray();
		$RelationRelated = $Relation->getRelated();
		$relatedClass = get_class($RelationRelated);
		$relatedModelKeyName = $RelationRelated->getKeyName(); // Primary key column name
		// related model should be validated already, no need to reload from DB by id
		if (isset($relationInput[$relatedModelKeyName])) {
			$RelatedModel = $relatedClass::findOrFail($relationInput[$relatedModelKeyName]);
		} else {
			$RelatedModel = new $relatedClass;
		}
		$RelatedModel->fill($relationInput);
		$morphableType = $Relation->getMorphType();
		$foreignKey = $Relation->getForeignKeyName();
		$RelatedModel->$morphableType = $Model->getClassAttribute();
		$RelatedModel->$foreignKey = $Model->id;
		self::saveModel($RelatedModel, $relationInput);
		//dd($RelatedModel);
		//$modelKeyName = $RelatedModel->getKeyName();
		//$relationInput[$modelKeyName] = $Model->$modelKeyName;
	}

	/**
	 * saveHasOneOrManyRelation helper function
	 *
	 * @param  BaseModel  $Model
	 * @param  HasOneOrMany  $Relation
	 * @param  array  $relationInput
	 */
	private static function saveHasRelation(BaseModel $Model, HasOneOrMany $Relation, & $relationInput) {

		if ($Model->exists !== true) {
			$Model->save();
		}
		// insert parent model into the input so that it can fill the related model
		$Model->unsetRelations(); // Temporary(?) fix as objects up the chain won't have attributes necessary for any virtual attributes they employ, thus breaking toArray()
		//$relationInput[$Model->snakeName()] = $Model->toArray();
		$relationInput[Str::snake(str_replace("_id","",$Relation->getForeignKeyName()))] = $Model->toArray();

		$RelationRelated = $Relation->getRelated();
		$relatedClass = get_class($RelationRelated);
		$relatedModelKeyName = $RelationRelated->getKeyName(); // Primary key column name
		//dd($Relation->getForeignKeyName());
		// related model should be validated already, no need to reload from DB by id
		if (isset($relationInput[$relatedModelKeyName])) {
			$RelatedModel = $relatedClass::findOrFail($relationInput[$relatedModelKeyName]);
		} else {
			$RelatedModel = new $relatedClass;
		}
		$RelatedModel->fill($relationInput);
		self::saveModel($RelatedModel, $relationInput);
		//$modelKeyName = $RelatedModel->getKeyName();
		//$relationInput[$modelKeyName] = $Model->$modelKeyName;
	}

	/**
	 * Delete any relations that are marked to cascade
	 *
	 * @param BaseModel $Model
	 */
	public static function deleteRelations(BaseModel $Model) {
		$cascades = self::cascades($Model);
		//kd($cascades);
		foreach ($cascades as $relationName => $cascadeType) {
			$Relation = $Model->$relationName();
			// TODO: Improve repeated code
			if ($cascadeType === self::CASCADE_UNLINK) {
				// TODO: Clean up pivot data (BelongsToMany)
				// only concerned with Has relations at the moment
				if (is_a($Relation, 'Illuminate\Database\Eloquent\Relations\HasOne') || is_a($Relation, 'Illuminate\Database\Eloquent\Relations\HasMany')) {
					foreach ($Relation->get() as $RelatedModel) {
						self::deleteRelations($RelatedModel);
					}
					$parentKey = $Relation->getParentKey();
					$foreignKey = $Relation->getForeignKey();
					$Relation->getRelated()->where($foreignKey, $parentKey)->update([$foreignKey => null]);
				} else if (is_a($Relation, 'Illuminate\Database\Eloquent\Relations\BelongsTo')) {
					// do nothing
				}
			} else if ($cascadeType === self::CASCADE_DELETE) {
				// TODO: Clean up pivot data (BelongsToMany)
				if (is_a($Relation, 'Illuminate\Database\Eloquent\Relations\HasOne') || is_a($Relation, 'Illuminate\Database\Eloquent\Relations\HasMany')) {
					foreach ($Relation->get() as $RelatedModel) {
						self::deleteRelations($RelatedModel);
					}
					$parentKey = $Relation->getParentKey();
					$foreignKey = $Relation->getForeignKey();
					$Relation->getRelated()->where($foreignKey, $parentKey)->delete();
				} else if (is_a($Relation, 'Illuminate\Database\Eloquent\Relations\BelongsTo')) {
					$RelatedModel = $Relation->first();

					if ($RelatedModel) {
						self::deleteRelations($RelatedModel);
						$RelatedModel->delete();
					}
				}
			}
		}
	}

	/**
	 * @param BaseModel $Model
	 * @return array
	 */
	private static function cascades(BaseModel $Model) {
		$cascades = [];
		// by default all fillable relations will just be unlinked
		foreach ($Model->fillableRelationships as $relationName) {
			$cascades[$relationName] = self::CASCADE_UNLINK;
		}
		// Model's cascades array overrides any of these
		foreach ($Model->cascades as $relationName => $cascadeType) {
			$cascades[$relationName] = $cascadeType;
		}
		return $cascades;
	}

	/**
	 * Run a callback for a model without any events being dispatched
	 *
	 * $model will be passed in to the $callable as the first argument
	 *
	 * Event dispatcher will be unset before running callback and then set again afterwards whether or not an exception
	 * is thrown
	 *
	 * @param Model    $model
	 * @param callable $callable
	 *
	 * @throws Exception
	 */
	public static function withoutEvents(Model $model, callable $callable) {
		$eventDispatcher = $model::getEventDispatcher();
		$model::unsetEventDispatcher();
		try {
			$callable($model);
			$model::setEventDispatcher($eventDispatcher);
		} catch (Exception $e) {
			$model::setEventDispatcher($eventDispatcher);
			throw $e;
		}
	}

	/**
	 * @param BaseModel $model
	 *
	 * @throws Exception
	 */
	public static function saveWithoutEvents(BaseModel $model) {
		$eventDispatcher = $model::getEventDispatcher();
		$model::unsetEventDispatcher();
		try {
			$model->save();
			$model::setEventDispatcher($eventDispatcher);
		} catch (Exception $e) {
			$model::setEventDispatcher($eventDispatcher);
			throw $e;
		}
	}

}
