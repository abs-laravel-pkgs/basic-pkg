<?php

namespace Abs\Basic;
use Illuminate\Database\Eloquent\Model;

class State extends Model {
	protected $table = 'states';
	protected $fillable = [
		'code',
		'name',
		'country_id',
	];

	public static function getStates($params) {
		$query = State::select('id', 'code', 'name', 'country_id')->orderBy('name');
		if ($params['country_id']) {
			$query->where('country_id', $params['country_id']);
		}
		$state_list = $query->get();

		return $state_list;
	}

}
