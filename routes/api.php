<?php

use Illuminate\Http\Request;

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

// Authenticated User API (Master)
Route::group(['middleware' => ['auth:api', 'activity'], 'prefix' => 'master', 'namespace' => 'Api'], function () {

    // Master User
    Route::get('/user', 'Master\UserController@index');
    Route::get('/user-list', 'Master\UserController@list');
    Route::post('/user', 'Master\UserController@store');
    Route::get('/user/{id}', 'Master\UserController@show');
    Route::get('/user-log/{id}', 'Master\UserController@log');
    Route::put('/user/{id}', 'Master\UserController@update');
    Route::delete('/user/{id}', 'Master\UserController@delete');
    Route::post('/multiple-delete-user', 'Master\UserController@multipleDelete');
    Route::get('/activate-user/{id}', 'Master\UserController@activate');
    Route::get('/block-user/{id}', 'Master\UserController@block');
    Route::get('/download-user-matrix', 'Master\UserController@userMatrixDownload');

});



// Authenticated User API (Dashboard)
Route::group(['middleware' => ['auth:api', 'activity'], 'prefix' => 'user', 'namespace' => 'Api'], function () {
    Route::get('/profile', 'ProfileController@profile');
    Route::post('/update-login-detail', 'ProfileController@updateLoginDetail');
    Route::post('/update-profile', 'ProfileController@updateProfile');
    Route::get('/login-history', 'ProfileController@loginHistory');
    Route::post('/change-photo-profile', 'ProfileController@changePhotoProfile');
    Route::post('/change-password', 'ProfileController@changePassword');
    Route::get('/module-object-list', 'ProfileController@moduleList');
});


