<?php

Route::group(['namespace' => 'Abs\Basic', 'middleware' => ['web'], 'prefix' => 'admin'], function () {

	Route::get('/admin/master/entity/list/{entity_type_id}', 'EntityController@entityList')->name('entityList');
});