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
			//CCC
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'EEE',
				'display_name' => 'CCC',
			],
			[
				'display_order' => 1,
				'parent' => 'EEE',
				'name' => 'add-FFF',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'EEE',
				'name' => 'edit-FFF',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'EEE',
				'name' => 'delete-FFF',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}