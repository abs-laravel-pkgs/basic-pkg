<?php

namespace Abs\Basic;
use Abs\Basic\Country;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// use Validator;

class CountryController extends Controller {

	public function __construct() {
	}

	public function getCountries(Request $r) {
		// $validator = Validator::make($r->all(), [
		// ]);
		// if ($validator->fails()) {
		// 	return response()->json([
		// 		'success' => false,
		// 		'error' => 'Validation errors',
		// 		'errors' => $validator->errors(),
		// 	], $this->successStatus);
		// }

		$this->data['success'] = 'true';
		$this->data['country_list'] = Country::getCountries();

		return response()->json($this->data);
	}

}
