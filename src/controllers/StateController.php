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

		$this->data['success'] = true;
		$this->data['state_list'] = State::getStates($r->all());

		return response()->json($this->data);
	}

}
