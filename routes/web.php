<?php

Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::get('/', 'BookmarksController@index');
    Route::get('/bookmarks/import', 'BookmarksController@importForm');
    Route::post('/bookmarks/import', 'BookmarksController@import');
});