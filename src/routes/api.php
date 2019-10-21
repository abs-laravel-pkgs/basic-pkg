<?php

Route::group(['middleware' => ['api']], function () {
	Route::group(['middleware' => ['auth:api'], 'prefix' => 'eyatra/api'], function () {
	});
});