<?php

Route::group(['namespace' => 'Abs\Basic', 'middleware' => ['web'], 'prefix' => 'admin'], function () {
	Route::get('/test', 'EntityController@test')->name('test');
	Route::get('/master/entity/list/{entity_type_id}', 'EntityController@entityList')->name('entityList');
});