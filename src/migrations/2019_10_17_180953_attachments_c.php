<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AttachmentsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('attachments', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('attachment_of_id');
			$table->unsignedInteger('attachment_type_id');
			$table->unsignedInteger('entity_id');
			$table->string('name', 255);

			$table->foreign('attachment_of_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('attachment_type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('attachments');
	}
}
