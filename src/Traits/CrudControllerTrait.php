<?php
namespace Abs\BasicPkg\Traits;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait CrudControllerTrait {

	public function beforeSave($Model, $input){

		if(Arr::get($input,'active' ) == false){
			$Model->deleted_at = Carbon::now();
		}
	}

}
