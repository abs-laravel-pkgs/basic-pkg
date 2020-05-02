<?php

namespace Abs\BasicPkg;

use Illuminate\Database\Eloquent\Model;

class Config extends Model {
	protected $fillable = [
		'id',
		'config_type_id',
		'name',
	];
	public $timestamps = false;

	public function configType() {
		return $this->belongsTo('App\ConfigType', 'config_type_id');
	}

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

}
