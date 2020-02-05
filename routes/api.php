<?php

use Illuminate\Http\Request;

<<<<<<< HEAD
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

Route::group(['prefix' => 'user', 'namespace' => 'Api'], function () {
    Route::post('/login', 'AuthController@authenticate');
    Route::post('/confirm-email/{id}', 'AuthController@confirmEmail');
    Route::post('/forgot-password', 'AuthController@forgotPassword');
});

Route::group(['middleware' => ['auth:api', 'activity'], 'prefix' => 'master', 'namespace' => 'Api'], function () {
    Route::post('/user', 'Master\UserController@store');
=======
Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
>>>>>>> ruben_dev
});

// Guest User API
Route::post('/login', 'Api\Auth\AuthController@login');
Route::post('/confirm-email/{id}', 'Api\Auth\AuthController@confirmEmail');
Route::post('/forgot-password', 'Api\Auth\AuthController@forgotPassword');


