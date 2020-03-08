<?php

namespace Abs\BasicPkg;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {
	protected $table = 'attachments';
	public $timestamps = false;
	protected $fillable = [
		'company_id',
		'attachment_of_id',
		'attachment_type_id',
		'entity_id',
		'name',
	];
}
