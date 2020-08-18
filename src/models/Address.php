<?php

namespace Abs\BasicPkg;
use Abs\HelperPkg\Traits\SeederTrait;
use App\BaseModel;
use App\City;
use App\Company;
use App\Config;
use App\Country;
use App\State;

class Address extends BaseModel {
	use SeederTrait;

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

	protected static $excelColumnRules = [
		'Address Of' => [
			'table_column_name' => 'address_of_id',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Config',
					'foreign_table_column' => 'name',
				],
			],
		],
		'Entity Username' => [
			'table_column_name' => 'entity_id',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Entity',
					'foreign_table_column' => 'name',
				],
			],
		],
		'Address Type' => [
			'table_column_name' => 'address_type_id',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Config',
					'foreign_table_column' => 'name',
				],
			],
		],
		'Name' => [
			'table_column_name' => 'name',
			'rules' => [
				'required' => [
				],
			],
		],
		'Address Line 1' => [
			'table_column_name' => 'address_line1',
			'rules' => [
				'required' => [
				],
			],
		],
		'Address Line 2' => [
			'table_column_name' => 'address_line2',
			'rules' => [

			],
		],
		'Country Code' => [
			'table_column_name' => 'country_id',
			'rules' => [
				'fk' => [
					'class' => 'App\Country',
					'foreign_table_column' => 'code',
				],
			],
		],
		'State Code' => [
			'table_column_name' => 'state_id',
			'rules' => [
				'fk' => [
					'class' => 'App\State',
					'foreign_table_column' => 'code',
				],
			],
		],
		'City Name' => [
			'table_column_name' => 'city_id',
			'rules' => [
				'fk' => [
					'class' => 'App\City',
					'foreign_table_column' => 'name',
				],
			],
		],
		'Pincode' => [
			'table_column_name' => 'pincode',
			'rules' => [

			],
		],
	];

	public static function saveFromObject($record_data) {
		$record = [
			'Company Code' => $record_data->company_code,
			'Address Of' => $record_data->address_of,
			'Entity Username' => $record_data->entity_username,
			'Address Type' => $record_data->address_type,
			'Name' => $record_data->name,
			'Address Line 1' => $record_data->address_line_1,
			'Address Line 2' => $record_data->address_line_2,
			'Country Code' => $record_data->country_code,
			'State Code' => $record_data->state_code,
			'City Name' => $record_data->city_name,
			'Pincode' => $record_data->pincode,

		];
		return static::saveFromExcelArray($record);
	}

	public static function saveFromExcelArray($record_data) {
		try {
			$errors = [];
			$company = Company::where('code', $record_data['Company Code'])->first();
			if (!$company) {
				return [
					'success' => false,
					'errors' => ['Invalid Company : ' . $record_data['Company Code']],
				];
			}

			if (empty($record_data['Address Of'])) {
				$errors[] = 'Address Of is empty.';
			} else {
				$address_of = Config::where([
					'name' => $record_data['Address Of'],
					'config_type_id' => 2,
				])->first();
				if (!$address_of) {
					$errors[] = 'Invalid Address Of : ' . $record_data['Address Of'];
				}
			}

			if (empty($record_data['Entity Username'])) {
				$errors[] = 'Entity Username is empty.';
			} else {
				$entity = Entity::where([
					'company_id' => $company->id,
					'name' => $record_data['Entity Username'],
				])->first();
				if (!$entity) {
					$errors[] = 'Invalid Entity Username : ' . $record_data['Entity Username'];
				}
			}
			if (empty($record_data['Address Type'])) {
				$errors[] = 'Address Type is empty.';
			} else {
				$address_type = Config::where([
					'name' => $record_data['Address Type'],
					'config_type_id' => 3,
				])->first();
				if (!$address_type) {
					$errors[] = 'Invalid Address Type : ' . $record_data['Address Type'];
				}
			}

			if (!empty($record_data['Country Code'])) {
				$country = Country::where([
					'code' => $record_data['Country Code'],
				])->first();
				if (!$country) {
					$errors[] = 'Invalid Country Code : ' . $record_data['Country Code'];
				}
			}

			if (!empty($record_data['State Code'])) {
				$state = State::where([
					'code' => $record_data['State Code'],
					'country_id' => $country->id,
				])->first();
				if (!$state) {
					$errors[] = 'Invalid State Code : ' . $record_data['State Code'];
				}
			}

			if (!empty($record_data['City Name'])) {
				$city = City::where([
					'name' => $record_data['City Name'],
					'state_id' => $state->id,
				])->first();
				if (!$city) {
					$errors[] = 'Invalid City Name : ' . $record_data['City Name'];
				}
			}

			if (count($errors) > 0) {
				return [
					'success' => false,
					'errors' => $errors,
				];
			}

			$record = Self::firstOrNew([
				'company_id' => $company->id,
				'name' => $record_data['Name'],
			]);

			$result = Self::validateAndFillExcelColumns($record_data, Static::$excelColumnRules, $record);
			if (!$result['success']) {
				return $result;
			}

			$record->company_id = $company->id;
			$record->address_of_id = $address_of->id;
			$record->entity_id = $entity->id;
			$record->address_type_id = $address_type->id;
			$record->country_id = $country->id;
			$record->state_id = $state->id;
			$record->city_id = $city->id;
			$record->save();
			return [
				'success' => true,
			];
		} catch (\Exception $e) {
			return [
				'success' => false,
				'errors' => [
					$e->getMessage(),
				],
			];
		}
	}

	protected $appends = [
		'formatted',
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

	public function getFormattedAttribute() {
		$formatted_address = '';
		$formatted_address .= !empty($this->address_line_1) ? $this->address_line_1 : '';
		$formatted_address .= !empty($this->address_line_2) ? ', ' . $this->address_line_2 : '';
		$formatted_address .= $this->city ? ', ' . $this->city->name : '';
		$formatted_address .= $this->state ? ', ' . $this->state->name : '';
		if ($this->state) {
			$formatted_address .= $this->state->country ? ', ' . $this->state->country->name : '';
		}
		$formatted_address .= $this->pincode ? ', ' . $this->pincode : '';
		return $formatted_address;
	}

	/*public static function createFromObject($record_data) {
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
	}*/
}
