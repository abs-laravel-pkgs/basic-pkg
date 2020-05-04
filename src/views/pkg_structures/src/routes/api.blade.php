<?php
Route::group(['namespace' => 'Abs\YYY\Api', 'middleware' => ['api', 'auth:api']], function () {
	Route::group(['prefix' => 'api/GGG'], function () {
		//Route::post('punch/status', 'PunchController@status');
	});
});