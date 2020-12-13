<?php

use Abs\BasicPkg\Controllers\Api\AttachmentPkgApiController;
use App\Http\Controllers\Api\Auth\AuthenticationController;

Route::group(['middleware' => ['api'], 'prefix' => '/api/auth'], function () {
	Route::post('login', AuthenticationController::class.'@login');
	Route::post('forgot-password', AuthenticationController::class.'@forgotPassword');

	Route::group(['middleware' => ['auth:api']], function () {

		Route::post('validate-token', AuthenticationController::class.'@validateToken');
		Route::post('change-password', AuthenticationController::class.'@changePassword');
		Route::get('logout', AuthenticationController::class.'@logout');
	});
});

Route::group(['middleware' => ['api','auth:api'], 'prefix' => '/api/attachment'], function () {
	Route::post('upload', AttachmentPkgApiController::class.'@upload');
});

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
