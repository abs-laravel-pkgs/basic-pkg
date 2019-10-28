<?php

namespace Abs\Basic;
use Illuminate\Database\Eloquent\Model;

class Country extends Model {
	protected $table = 'countries';
	protected $fillable = [
		'code',
		'name',
	];

	public static function getCountries() {
		$query = Country::select('id', 'code', 'name', 'has_state_list')->orderBy('name');
		$country_list = $query->get();

		return $country_list;
	}
}
