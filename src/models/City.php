<?php

namespace Abs\Basic;
use Illuminate\Database\Eloquent\Model;

class City extends Model {
	protected $table = 'cities';
	protected $fillable = [
		'code',
		'name',
		'state_id',
	];
}
