<?php

namespace Abs\Basic;

use Illuminate\Database\Eloquent\SoftDeletes;
use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole {
	use SoftDeletes;

	Protected $fillable = [
		'id',
		'company_id',
		'name',
		'display_order',
		'display_name',
		'description',
		'is_hidden',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function users() {
		return $this->belongsToMany('App\User');
	}

	public function permissions() {
		return $this->belongsToMany('App\Permission', 'permission_role', 'role_id');
	}

	public function createdBy() {
		return $this->belongsTo('App\User', 'created_by_id', 'id');
	}

	public function updatedBy() {
		return $this->belongsTo('App\User', 'created_by_id', 'id');
	}

	public function deleteBy() {
		return $this->belongsTo('App\User', 'created_by_id', 'id');
	}

	public function company() {
		return $this->belongsTo('App\Company', 'company_id', 'id');
	}

}