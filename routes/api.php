<?php

use Illuminate\Support\Facades\Route;

/**
 * Api Routes
 */

Route::group(['prefix' => '/v1', 'middleware' => 'auth:api'], function() {
    Route::     get('/bookmarks/folderTree',    'Api\BookmarksController@folderTree');
    Route::     get('/bookmarks/search/{term}', 'Api\BookmarksController@search');
    Route::    post('/bookmarks/bulkDelete',    'Api\BookmarksController@bulkDelete');
    Route::    post('/bookmarks/bulkMove',      'Api\BookmarksController@bulkMove');

    Route::resource('/bookmarks',               'Api\BookmarksController');
});

//
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
