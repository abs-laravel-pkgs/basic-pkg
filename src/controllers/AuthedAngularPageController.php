<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthedAngularPageController extends Controller {

	public function authedAngularPage(Request $r) {
		$this->data['theme'] = config('custom.theme');
		return view('themes/' . $this->data['theme'] . '/pages/authed-angular-page', $this->data);
	}
}
