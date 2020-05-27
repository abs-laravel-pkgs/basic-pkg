<?php

namespace Abs\BasicPkg;
use App\City;
use App\Company;
use App\Config;
use App\Country;
use App\Outlet;
use App\State;
use Illuminate\Database\Eloquent\Model;

class Address extends Model {
	protected $table = 'addresses';
	public $timestamps = false;
	protected $fillable = [
		'name',
		'address_line_1',
		'address_line_2',
		'state_id',
		'city_id',
		'country_id',
		'pincode',
	];

	public function country() {
		return $this->belongsTo('App\Country');
	}

	public function state() {
		return $this->belongsTo('App\State');
	}

	public function city() {
		return $this->belongsTo('App\City');
	}

	public function getFormattedAddressAttribute() {
		$formatted_address = '';
		$formatted_address .= !empty($this->address_line_1) ? $this->address_line_1 : '';
		$formatted_address .= !empty($this->address_line_2) ? ', ' . $this->address_line_2 : '';
		$formatted_address .= $this->city ? ', ' . $this->city->name : '';
		$formatted_address .= $this->state ? ', ' . $this->state->name : '';
		$formatted_address .= $this->state->country ? ', ' . $this->state->country->name : '';
		$formatted_address .= $this->pincode ? ', ' . $this->pincode : '';
		return $formatted_address;
	}

	public static function createFromObject($record_data) {
		$company = Company::where('code', $record_data->company)->first();
		$admin = $company->admin();

		$errors = [];
		if (!$company) {
			$errors[] = 'Invalid Company : ' . $record_data->company;
		}

		$address_of = Config::where('name', $record_data->address_of)->where('config_type_id', 2)->first();
		if (!$address_of) {
			$errors[] = 'Invalid address of : ' . $record_data->address_of;
		} else {
			if ($address_of->id == 22) {
				$entity = Outlet::where('code', $record_data->entity)->where('company_id', $company->id)->first();
			} else if ($address_of->id == 23) {
				$entity = Company::where('code', $record_data->entity)->first();
			} else if ($address_of->id == 24) {
				$entity = Customer::where('code', $record_data->entity)->where('company_id', $company->id)->first();
			}
			if (!$entity) {
				$errors[] = 'Invalid entity : ' . $record_data->entity;
			}
		}

		$address_type = Config::where('name', $record_data->address_type)->where('config_type_id', 3)->first();
		if (!$address_type) {
			$errors[] = 'Invalid address type : ' . $record_data->address_type;
		}

		$country = Country::where('code', $record_data->country)->first();
		if (!$country) {
			$errors[] = 'Invalid country : ' . $record_data->country;
		} else {
			$state = State::where('code', $record_data->state)->where('country_id', $country->id)->first();
			if (!$state) {
				$errors[] = 'Invalid state : ' . $record_data->state;
			} else {
				$city = City::where('name', $record_data->city)->where('state_id', $state->id)->first();
				if (!$city) {
					$errors[] = 'Invalid city : ' . $record_data->city;
				}
			}
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'address_of_id' => $address_of->id,
			'entity_id' => $entity->id,
			'address_type_id' => $address_type->id,
		]);
		$record->name = $record_data->name;
		$record->address_line1 = $record_data->address_line_1;
		$record->address_line2 = $record_data->address_line_2;
		$record->country_id = $country->id;
		$record->state_id = $state->id;
		$record->city_id = $city->id;
		$record->pincode = $record_data->pincode;
		$record->save();
		return $record;
	}
}
