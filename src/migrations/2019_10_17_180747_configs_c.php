<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConfigsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('configs')) {

			Schema::create('configs', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('config_type_id');
				$table->string('name', 191);

				$table->foreign('config_type_id')->references('id')->on('config_types')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["config_type_id", "name"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('configs');
	}
}
