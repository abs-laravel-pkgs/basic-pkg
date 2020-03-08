<?php
namespace Abs\BasicPkg\Database\Seeds;

use Abs\BasicPkg\Country;
use Excel;
use Illuminate\Database\Seeder;

class CompanyExcelPkgSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		ini_set('memory_limit', -1);

		$file_name = $this->command->ask("Enter Excel File Name (placed in public/excel-imports/)", 'countries');
		$excel_file_path = 'public/excel-imports/' . $file_name . '.xlsx';
		$sheets = [];
		Excel::selectSheetsByIndex(0)->load($excel_file_path, function ($reader) use (&$sheets) {
			$reader->limitColumns(4);
			$reader->limitRows(300);
			$records = $reader->get();
			Country::createMultipleFromArray($records);
		});

	}
}