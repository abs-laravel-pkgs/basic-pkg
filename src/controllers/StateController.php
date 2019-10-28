<?php

namespace Abs\Basic;
use Abs\Basic\State;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class StateController extends Controller {

	public function __construct() {
	}

	public function getStates(Request $r) {
		$validator = Validator::make($r->all(), [
			'country_id' => 'nullable|exists:countries,id',
		]);
		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'error' => 'Validation errors',
				'errors' => $validator->errors(),
			], $this->successStatus);
		}

		$query = State::from('states');

		if ($r->country_id) {
			$country = Country::find($r->country_id);
			if (!$country) {
				return response()->json(['success' => false, 'errors' => ['Invalid country']]);
			}
			$query->where('country_id', $country->id);
		}

		$states = $query->get();
		return response()->json(['success' => true, 'states' => $states]);
	}

}
