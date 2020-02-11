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

    /** Master User */
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

    /** User Profile */
    Route::get('/user-profile-param', 'Master\UserController@profileParam');
    Route::post('/user-profile-param/{id}', 'Master\UserController@storeUserProfileParam');
    Route::post('/user/{id}/profile', 'Master\UserController@storeProfile');
    Route::post('/user/{id}/photo-profile', 'Master\UserController@storePhotoProfile');

    /** User Role */
    Route::post('/user/{id}/asign-role', 'Master\UserController@assignRole');
    Route::get('/user/{id}/role-list', 'Master\UserController@userRoleList');
    Route::post('/delete-user-role/{id}', 'Master\UserController@deleteUserRole');

    /** User Login History */
    Route::get('/user/{id}/login-history', 'Master\UserController@userLoginHistory');

    /** Role */
    Route::get('/role', 'Master\RoleController@index');
    Route::get('/role-list', 'Master\RoleController@list');
    Route::post('/role', 'Master\RoleController@store');
    Route::post('/copy-role', 'Master\RoleController@copy');
    Route::get('/composite-role/{id}', 'Master\RoleController@compositeList');
    Route::post('/composite-role/{id}', 'Master\RoleController@composite');
    Route::post('/composite-role-delete/{id}', 'Master\RoleController@uncomposite');
    Route::get('/role/{id}', 'Master\RoleController@show');
    Route::get('/role-log/{id}', 'Master\RoleController@log');
    Route::put('/role/{id}', 'Master\RoleController@update');
    Route::delete('/role/{id}', 'Master\RoleController@delete');
    Route::post('/multiple-delete-role', 'Master\RoleController@multipleDelete');

    /** Role Auth Object */
    Route::post('/role/{id}/add-auth-object', 'Master\RoleController@storeRoleAuthObject');
    Route::get('/role/{id}/auth-object-list', 'Master\RoleController@roleAuthObjectList');
    Route::put('/role/{id}/edit-auth-object', 'Master\RoleController@updateRoleAuthObject');

    /** Role Organization Parameter */
    Route::post('/role/{id}/add-organization-parameter', 'Master\RoleController@storeRoleOrgParam');
    Route::get('/role/{id}/organization-parameter-list', 'Master\RoleController@roleOrgParamList');
    Route::put('/role/{id}/edit-organization-parameter', 'Master\RoleController@updateRoleOrgParam');

    /** Role User */
    Route::post('/role/{id}/asign-user', 'Master\RoleController@assignUser');
    Route::get('/role/{id}/user-list', 'Master\RoleController@roleUserList');
    Route::post('/delete-role-user/{id}', 'Master\RoleController@deleteRoleUser');

    /** Modules (Auth Object) */
    Route::get('/all-modules', 'Master\AuthObjectController@allData');
    Route::get('/modules', 'Master\AuthObjectController@index');
    Route::post('/modules', 'Master\AuthObjectController@stores');
    Route::get('/modules/{id}', 'Master\AuthObjectController@show');
    Route::put('/modules/{id}', 'Master\AuthObjectController@update');
    Route::delete('/modules/{id}', 'Master\AuthObjectController@delete');

    /** Classification Type */
    Route::get('/classification-type', 'Master\ClassificationTypeController@index');
    Route::post('/classification-type', 'Master\ClassificationTypeController@store');
    Route::get('/classification-type/{id}', 'Master\ClassificationTypeController@show');
    Route::put('/classification-type/{id}', 'Master\ClassificationTypeController@update');
    Route::delete('/classification-type/{id}', 'Master\ClassificationTypeController@delete');
    Route::post('/multiple-delete-classification-type', 'Master\ClassificationTypeController@multipleDelete');

     // Settings
     Route::get('/setting', 'Master\SettingController@index');
     Route::get('/setting/{key}', 'Master\SettingController@show');
     Route::post('/setting/{key}', 'Master\SettingController@update');
     
    /** MASTER - RELEASE STRATEGY */
    //Release Object
    Route::get('/release-object', 'Master\ReleaseStrategy\ReleaseObjectController@index');
    Route::get('/release-object-list', 'Master\ReleaseStrategy\ReleaseObjectController@list');
    Route::post('/release-object', 'Master\ReleaseStrategy\ReleaseObjectController@store');
    Route::get('/release-object/{id}', 'Master\ReleaseStrategy\ReleaseObjectController@show');
    Route::put('/release-object/{id}', 'Master\ReleaseStrategy\ReleaseObjectController@update');
    Route::delete('/release-object/{id}', 'Master\ReleaseStrategy\ReleaseObjectController@delete');
    Route::post('/multiple-delete-release-object', 'Master\ReleaseStrategy\ReleaseObjectController@multipleDelete');

    // Release Group
    Route::get('/classification-for-workflow', 'Material\MaterialController@getClassificationForWorkflow');
    Route::get('/release-group', 'Master\ReleaseStrategy\ReleaseGroupController@index');
    Route::get('/release-group-list', 'Master\ReleaseStrategy\ReleaseGroupController@list');
    Route::post('/release-group', 'Master\ReleaseStrategy\ReleaseGroupController@store');
    Route::get('/release-group/{id}', 'Master\ReleaseStrategy\ReleaseGroupController@show');
    Route::put('/release-group/{id}', 'Master\ReleaseStrategy\ReleaseGroupController@update');
    Route::delete('/release-group/{id}', 'Master\ReleaseStrategy\ReleaseGroupController@delete');
    Route::post('/multiple-delete-release-group', 'Master\ReleaseStrategy\ReleaseGroupController@multipleDelete');

    // Release Code
    Route::get('/release-code', 'Master\ReleaseStrategy\ReleaseCodeController@index');
    Route::get('/release-code-list', 'Master\ReleaseStrategy\ReleaseCodeController@list');
    Route::post('/release-code', 'Master\ReleaseStrategy\ReleaseCodeController@store');
    Route::get('/release-code/{id}', 'Master\ReleaseStrategy\ReleaseCodeController@show');
    Route::put('/release-code/{id}', 'Master\ReleaseStrategy\ReleaseCodeController@update');
    Route::delete('/release-code/{id}', 'Master\ReleaseStrategy\ReleaseCodeController@delete');
    Route::post('/multiple-delete-release-code', 'Master\ReleaseStrategy\ReleaseCodeController@multipleDelete');

    // Release Code User
    Route::get('/release-code-user', 'Master\ReleaseStrategy\ReleaseCodeUserController@index');
    Route::get('/release-code-user-list', 'Master\ReleaseStrategy\ReleaseCodeUserController@list');
    Route::post('/release-code-user', 'Master\ReleaseStrategy\ReleaseCodeUserController@store');
    Route::get('/release-code-user/{id}', 'Master\ReleaseStrategy\ReleaseCodeUserController@show');
    Route::put('/release-code-user/{id}', 'Master\ReleaseStrategy\ReleaseCodeUserController@update');
    Route::delete('/release-code-user/{id}', 'Master\ReleaseStrategy\ReleaseCodeUserController@delete');
    Route::post('/multiple-delete-release-code-user', 'Master\ReleaseStrategy\ReleaseCodeUserController@multipleDelete');

    // Release strategy
    Route::get('/release-strategy', 'Master\ReleaseStrategy\ReleaseStrategyController@index');
    Route::get('/release-strategy-list', 'Master\ReleaseStrategy\ReleaseStrategyController@list');
    Route::post('/release-strategy', 'Master\ReleaseStrategy\ReleaseStrategyController@store');
    Route::get('/release-strategy/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@show');
    Route::get('/release-strategy-activate/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@activate');
    Route::get('/release-strategy-deactivate/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@deactivate');
    Route::put('/release-strategy/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@update');
    Route::delete('/release-strategy/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@delete');
    Route::post('/multiple-delete-release-strategy', 'Master\ReleaseStrategy\ReleaseStrategyController@multipleDelete');

    // release strategy assign release code
    Route::post('/release-strategy/{id}/assign-code', 'Master\ReleaseStrategy\ReleaseStrategyController@assignCode');
    Route::post('/release-strategy/{id}/add-release-code', 'Master\ReleaseStrategy\ReleaseStrategyController@addCode');
    Route::post('/release-strategy/{id}/delete-release-code', 'Master\ReleaseStrategy\ReleaseStrategyController@deleteCode');

    /** Release Strategy Classification */
    Route::post('/release-strategy/{id}/classification', 'Master\ReleaseStrategy\ReleaseStrategyController@storeClassification');

    /** Release Strategy Status */
    Route::get('/release-strategy/{id}/status', 'Master\ReleaseStrategy\ReleaseStrategyController@status');
    Route::post('/release-strategy/{id}/status', 'Master\ReleaseStrategy\ReleaseStrategyController@updateStatus');

    /** Setting */
    Route::get('/setting', 'Master\SettingController@index');
    Route::get('/setting/{key}', 'Master\SettingController@show');
    Route::post('/setting/{key}', 'Master\SettingController@update');

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


