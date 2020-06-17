<?php
namespace Abs\BasicPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class BasicPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'static',
				'display_name' => 'Static Pages',
			],
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'design-input',
				'display_name' => 'Design Input',
			],
		];
		Permission::createFromArrays($permissions);
	}
}