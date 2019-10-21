<?php

namespace Abs\Basic;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {
	protected $table = 'attachments';
	public $timestamps = false;
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
