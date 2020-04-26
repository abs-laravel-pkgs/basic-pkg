<?php

namespace Abs\BasicPkg;

use Abs\BasicPkg\Traits\BasicTrait;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filter extends Model {
	use SoftDeletes;
	use BasicTrait;
	protected $table = 'filters';
	protected $fillable = [
		'id',
		'user_id',
		'page_id',
		'name',
		'value',
		'created_by_id',
	];

	public function user() {
		return $this->belongsTo('App\User');
	}

	public function page() {
		return $this->belongsTo('App\Config', 'page_id');
	}

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

	public static function getList($page_id, $add_default = true, $default_text = 'Select User') {
		$list = Collect(Self::select([
			'id',
			'name',
		])
				->where(function ($q) {
					$q->where('user_id', Auth::id())
						->orWhereNull('user_id')
					;
				})
				->orderBy('user_id')
				->orderBy('name')
				->get()
		);
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}

}
