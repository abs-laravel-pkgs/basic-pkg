<?php

Route::group(['namespace' => 'Abs\BasicPkg', 'middleware' => ['web']], function () {
	Route::get('theme/', 'ThemePageController@themeGuideHome')->name('themeGuideHome');
});