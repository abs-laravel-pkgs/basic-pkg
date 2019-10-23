<?php

Route::group(['namespace' => 'Abs\Basic', 'middleware' => ['web'], 'prefix' => 'admin'], function () {
	Route::get('/test', 'EntityController@test')->name('test');
	Route::get('/master/entity/list/{entity_type_id}', 'EntityController@entityList')->name('entityList');
	Route::post('/countries/get', 'CountryController@getCountries')->name('getCountries');
	Route::post('/state/get', 'StateController@getStates')->name('getStates');

});