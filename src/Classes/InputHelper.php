<?php
namespace Abs\BasicPkg\Classes;

use Illuminate\Support\Facades\Input;
use Request;

class InputHelper {

	public static function getJsonPost() {
		return (array)json_decode(
			Request::instance()->getContent()
		);
	}

	public static function replaceWithJson($json = null) {
		if (!$json) {
			$json = self::getJsonPost();
		}
		Input::replace($json);
	}

	public static function checkAndReplaceInput() {
		if (!Input::all()) {
			self::replaceWithJson();
		}
	}

}
