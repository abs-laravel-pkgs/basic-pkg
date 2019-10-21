<?php

// Route::post('basic/api/login', 'Abs\Basic\API\AuthController@login');

Route::group(['namespace' => 'Abs\Basic\API', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'basic/api'], function () {

		Route::post('login', 'AuthController@login');

		Route::group(['middleware' => ['auth:api'], 'prefix' => 'basic/api'], function () {
		});

	});
});