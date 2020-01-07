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

	public static function createMultipleFromArray($records) {
		foreach ($records as $key => $data) {
			try {
				$data = $data->toArray();
				$record = self::firstOrNew([
					'code' => $data['code'],
				]);
				$record->fill($data);
				$record->save();
			} catch (\Exception $e) {
				dump($data, $e->getMessage());
			}
		}
	}
}
