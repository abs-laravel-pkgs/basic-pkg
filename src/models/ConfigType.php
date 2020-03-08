<?php

namespace Abs\BasicPkg;

use Illuminate\Database\Eloquent\Model;

class ConfigType extends Model {
	protected $fillable = ['id', 'name'];
	public $timestamps = false;
}
