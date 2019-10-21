<?php

namespace Abs\Basic;
use Illuminate\Database\Eloquent\Model;

class Address extends Model {
	protected $table = 'addresses';
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
