<?php

namespace Abs\BasicPkg\Models;

use Illuminate\Database\Eloquent\Model;

class EntityType extends Model {
	protected $fillable = [
		'id',
		'name',
	];
}
