<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FiltersU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('filters', function (Blueprint $table) {
			$table->unsignedInteger('user_id')->nullable()->change();
			$table->boolean('is_default')->default(0)->after('name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('filters', function (Blueprint $table) {
			$table->dropColumn('is_default');
		});
	}
}
