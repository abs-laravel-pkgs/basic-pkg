<?php

namespace Abs\BasicPkg\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {
	protected $table = 'attachments';
	protected $fillable = [
		'company_id',
		'attachment_of_id',
		'attachment_type_id',
		'entity_id',
		'name',
	];
	public $timestamps = false;
}
