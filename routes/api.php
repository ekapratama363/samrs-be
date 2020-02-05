<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

// Guest User API
Route::post('/login', 'Api\Auth\AuthController@login');
Route::post('/confirm-email/{id}', 'Api\Auth\AuthController@confirmEmail');
Route::post('/forgot-password', 'Api\Auth\AuthController@forgotPassword');


