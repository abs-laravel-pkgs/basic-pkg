<?php

namespace Abs\Basic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model {
	use SoftDeletes;
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

}
