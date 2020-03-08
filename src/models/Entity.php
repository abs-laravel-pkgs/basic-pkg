<?php

namespace Abs\BasicPkg;

use Abs\BasicPkg\Traits\BasicTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model {
	use SoftDeletes;
	use BasicTrait;
	protected $table = 'entities';
	protected $fillable = [
		'id',
		'company_id',
		'entity_type_id',
		'name',
		'created_by_id',
	];

	public static function create($sample_entities, $admin, $company) {
		foreach ($sample_entities as $entity_type_id => $entities) {
			foreach ($entities as $entity_name) {
				$record = Entity::firstOrNew([
					'entity_type_id' => $entity_type_id,
					'company_id' => $company->id,
					'name' => $entity_name,
				]);
				$record->created_by_id = $admin->id;
				$record->save();
			}

		}
	}

	public static function createMultipleEntityFromCollection($items, $company_id, $admin, $entity_type_id, $name) {
		foreach ($items as $key => $item) {
			try {
				if (!$item->{$name}) {
					// dump('empty main_category_name');
					continue;
				}

				$record = Entity::firstOrNew([
					'entity_type_id' => $entity_type_id,
					'company_id' => $company_id,
					'name' => $item->{$name},
				]);
				$record->created_by_id = $admin->id;
				$record->save();
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

	public static function createFromObject($record_data, $company = null) {

		$errors = [];
		if (!$company) {
			$company = Company::where('code', $record_data->company_code)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company_code);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$entity_type = EntityType::where([
			'name' => $record_data->entity_type,
		])->first();
		if (!$entity_type) {
			dump('Invalid entity_type : ' . $record_data->entity_type);
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'entity_type_id' => $entity_type->id,
			'name' => $record_data->name,
		]);
		$record->created_by_id = $admin->id;
		if ($record_data->status != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->save();
	}
}
