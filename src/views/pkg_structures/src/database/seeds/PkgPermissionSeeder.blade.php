<?php
namespace Abs\YYY\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class YYYPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			MMM,
		];
		Permission::createFromArrays($permissions);
	}
}