<?php

namespace Abs\Basic;

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

}
