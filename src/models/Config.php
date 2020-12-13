<?php

namespace Abs\BasicPkg\Models;

use Abs\BasicPkg\Models\BaseModel;

class Config extends BaseModel {
	protected $fillable = [
		'id',
		'config_type_id',
		'name',
	];

	// Relations --------------------------------------------------------------

	public function configType() {
		return $this->belongsTo('App\ConfigType', 'config_type_id');
	}

	// Query Scopes --------------------------------------------------------------

	public function scopeFilterConfigType($query, $config_type_id) {
		$query->where('config_type_id', $config_type_id);
	}

	public function scopeFilterSearch($query, $term) {
		if (strlen($term)) {
			$query->where(function ($query) use ($term) {
				$query->orWhere('name', 'LIKE', '%' . $term . '%');
			});
		}
	}

	// Static Operations --------------------------------------------------------------

	public static function getList($type_id, $add_default = true, $default_text = 'Select') {
		$list = Collect(Self::select([
			'id',
			'name',
		])
				->where('config_type_id', $type_id)
				->orderBy('name')
				->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}

	public static function getDropDownList($params = []) {
		$list = Self::select([
			'id',
			'name',
		])
			->where(function ($q) use ($params) {
				if (isset($params['config_type_id'])) {
					$q->where('config_type_id', $params['config_type_id']);
				}
			});
		if (isset($params['orderBy']) && $params['orderBy'] == 'id') {
			$list->orderBy('id');
		} else {
			$list->orderBy('name');
		}
		$list = collect($list->get());
		if (!isset($params['add_default'])) {
			$params['add_default'] = true;
		}
		if (isset($params['add_default']) && $params['add_default']) {
			if (isset($params['default_text']) && $params['default_text']) {
				$default_text = $params['default_text'];
			} else {
				$default_text = 'Select';
			}
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}

}
