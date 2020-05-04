<?php

Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'GGG'], function () {

	//DDD
	Route::get('/FFF/get-list', 'ZZZController@getZZZList')->name('getZZZList');
	Route::get('/FFF/get-form-data', 'ZZZController@getZZZFormData')->name('getZZZFormData');
	Route::post('/FFF/save', 'ZZZController@saveZZZ')->name('saveZZZ');
	Route::get('/FFF/delete', 'ZZZController@deleteZZZ')->name('deleteZZZ');
	Route::get('/FFF/get-filter-data', 'ZZZController@getZZZFilterData')->name('getZZZFilterData');

});