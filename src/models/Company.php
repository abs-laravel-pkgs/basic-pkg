<?php

namespace Abs\Basic;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model {
	use SoftDeletes;
	protected $table = 'companies';
	protected $fillable = [
		'code',
		'name',
		'address_id',
		'logo_id',
		'contact_number',
		'email',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function admin() {
		$admin = User::where('username', $this->code . 'a1')->where('company_id', $this->id)->first();
		if (!$admin) {
			dd('Default admin not found');
		}
		return $admin;
	}

	public static function createFromCollection($records, $company = null) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->code) {
					continue;
				}
				$record = self::createFromObject($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

	public static function createFromObject($record_data, $company = null) {
		$record = self::firstOrNew([
			'id' => $record_data->id,
		]);
		$record->code = $record_data->code;
		$record->name = $record_data->name;
		$record->contact_number = $record_data->contact_number;
		$record->email = $record_data->email;
		$record->save();

		$user = User::firstOrNew([
			'company_id' => $record->id,
			'username' => $record->code . 'a1',
		]);
		$user->user_type_id = 1;
		$user->entity_id = null;
		$user->first_name = $record->name;
		$user->last_name = 'Admin 1';
		$user->email = $record->code . 'a1@' . $record->code . '.com';
		$user->password = $record_data->password; //'$2y$10$N9pYzAbL2spl7vX3ZE1aBeekppaosAdixk04PTkK5obng7.KsLAQ2'; //
		$user->mobile_number = $record_data->mobile_number;
		$user->save();
		return $record;
	}

}
