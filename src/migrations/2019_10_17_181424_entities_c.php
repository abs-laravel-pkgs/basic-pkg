<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EntitiesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('entities', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('company_id')->nullable();
			$table->unsignedInteger('entity_type_id');
			$table->string('name', 191);
			$table->unsignedInteger('created_by_id')->nullable();
			$table->unsignedInteger('updated_by_id')->nullable();
			$table->unsignedInteger('deleted_by_id')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('entity_type_id')->references('id')->on('entity_types')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

			$table->unique(["company_id", "entity_type_id", "name"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('entities');
	}
}
