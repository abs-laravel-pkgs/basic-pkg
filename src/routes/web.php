<?php

Route::group(['namespace' => 'Abs\BasicPkg', 'middleware' => ['web']], function () {
	Route::get('theme/', 'ThemePageController@themeGuideHome')->name('themeGuideHome');
	// Route::post('/countries/get', 'CountryController@getCountries')->name('getCountries');
	// Route::post('/state/get', 'StateController@getStates')->name('getStates');

});