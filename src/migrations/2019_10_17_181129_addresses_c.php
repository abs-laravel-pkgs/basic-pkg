<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddressesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('addresses')) {

			Schema::create('addresses', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('company_id');
				$table->unsignedInteger('address_of_id');
				$table->unsignedInteger('entity_id');
				$table->unsignedInteger('address_type_id')->nullable();
				$table->string('name', 191);
				$table->unsignedInteger('contact_person_id')->nullable();
				$table->string('address_line_1', 255);
				$table->string('address_line_2', 255)->nullable();
				$table->unsignedInteger('state_id')->nullable();
				$table->unsignedInteger('city_id')->nullable();
				$table->unsignedInteger('country_id');
				$table->string('pincode', 10)->nullable();

				$table->foreign('address_of_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('address_type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["entity_id", "address_of_id", "name"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('addresses');
	}
}
