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
				$table->string('addressable_type');
				$table->unsignedInteger('addressable_id');
				$table->unsignedInteger('address_type_id')->nullable();
				$table->string('name', 191);
				$table->unsignedInteger('contact_person_id')->nullable();
				$table->string('address_line_1', 255);
				$table->string('address_line_2', 255)->nullable();
				$table->unsignedInteger('district_id')->nullable();
				$table->unsignedInteger('sub_district_id')->nullable();
				$table->unsignedInteger('city_id')->nullable();
				$table->string('pincode', 10);

				$table->foreign('address_type_id')->references('id')->on('configs')->onUpdate('cascade');
				$table->foreign('district_id')->references('id')->on('district')->onUpdate('cascade');
				$table->foreign('sub_district_id')->references('id')->on('sub_districts')->onUpdate('cascade');
				$table->foreign('city_id')->references('id')->on('cities');
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
