<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThemePageController extends Controller {

	public function themeGuideHome(Request $r) {
		$this->data['theme'] = config('custom.theme');
		return view($this->data['theme'] . '-pkg::elements', $this->data);
	}
}
