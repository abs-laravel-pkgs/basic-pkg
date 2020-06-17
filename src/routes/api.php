<?php

Route::group(['namespace' => 'Abs\BasicPkg\API', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'basic/api'], function () {

		Route::post('login', 'AuthController@login');

		Route::group(['middleware' => ['auth:api'], 'prefix' => 'basic/api'], function () {
		});

	});
});

Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware' => ['auth:api']], function () {
	Route::group(['prefix' => 'api'], function () {

		Route::group(['prefix' => 'config'], function () {
			$controller = 'ConfigController';
			Route::get('index', $controller . '@index');
			Route::get('read/{id}', $controller . '@read');
			Route::post('save', $controller . '@save');
			Route::post('remove', $controller . '@remove');
			Route::get('options', $controller . '@options');
		});

	});
});
