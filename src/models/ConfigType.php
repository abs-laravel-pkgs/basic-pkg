<?php

namespace Abs\BasicPkg\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigType extends Model {
	public $timestamps = false;
	protected $fillable = ['id', 'name'];
}
