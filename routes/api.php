<?php

use App\Http\Controllers\Api\BookmarksController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => '/v1', 'middleware' => 'auth:api'], function() {
    Route::get('/bookmarks/folderTree', [BookmarksController::class, 'folderTree']);
    Route::get('/bookmarks/search/{term}', [BookmarksController::class, 'search']);
    Route::post('/bookmarks/bulkDelete', [BookmarksController::class, 'bulkDelete']);
    Route::post('/bookmarks/bulkMove', [BookmarksController::class, 'bulkMove']);

    Route::resource('/bookmarks',BookmarksController::class);
});
