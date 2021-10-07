<?php

use App\Http\Controllers\BookmarksController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::get('/', [BookmarksController::class, 'index']);
    Route::get('/bookmarks/import', [BookmarksController::class, 'importForm']);
    Route::post('/bookmarks/import', [BookmarksController::class, 'import']);
});
