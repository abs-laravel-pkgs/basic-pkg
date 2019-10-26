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

	public function city() {
		return $this->belongsTo('Abs\Basic\City', 'city_id');
	}

	public function state() {
		return $this->belongsTo('Abs\Basic\State', 'state_id');
	}

	public function country() {
		return $this->belongsTo('Abs\Basic\Country', 'country_id');
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
}
