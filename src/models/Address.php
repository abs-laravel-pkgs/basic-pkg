<?php

namespace Abs\Basic;
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
}
