<?php

Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::get('/', 'BookmarksController@index');
});