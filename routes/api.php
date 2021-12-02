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
Route::group(['middleware' => ['auth:api', 'activity'], 'prefix' => 'master', 'namespace' => 'Api'], function () {
    /* User Group */
    Route::get('/user-group', 'Master\UserGroupController@index');
    Route::get('/user-group-list', 'Master\UserGroupController@list');
    Route::post('/user-group', 'Master\UserGroupController@store');
    Route::get('/user-group/{id}', 'Master\UserGroupController@show');
    Route::get('/user-group-log/{id}', 'Master\UserGroupController@log');
    Route::put('/user-group/{id}', 'Master\UserGroupController@update');
    Route::delete('/user-group/{id}', 'Master\UserGroupController@delete');
    Route::post('/multiple-delete-user-group', 'Master\UserGroupController@multipleDelete');

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

    // /** Classification Type */
    // Route::get('/classification-type', 'Master\ClassificationTypeController@index');
    // Route::post('/classification-type', 'Master\ClassificationTypeController@store');
    // Route::get('/classification-type/{id}', 'Master\ClassificationTypeController@show');
    // Route::put('/classification-type/{id}', 'Master\ClassificationTypeController@update');
    // Route::delete('/classification-type/{id}', 'Master\ClassificationTypeController@delete');
    // Route::post('/multiple-delete-classification-type', 'Master\ClassificationTypeController@multipleDelete');

    // /* Classification Material */
    // Route::get('/classification-material', 'Master\ClassificationController@index');
    // Route::get('/classification-material-list', 'Master\ClassificationController@list');
    // Route::post('/classification-material', 'Master\ClassificationController@store');
    // Route::get('/classification-material/{id}', 'Master\ClassificationController@show');
    // Route::get('/classification-material-log/{id}', 'Master\ClassificationController@log');
    // Route::put('/classification-material/{id}', 'Master\ClassificationController@update');
    // Route::delete('/classification-material/{id}', 'Master\ClassificationController@delete');
    // Route::post('/multiple-delete-classification-mat', 'Master\ClassificationController@multipleDelete');

    // /* Classification Parameter */
    // Route::post('/classification-material/{id}/addparam', 'Master\ClassificationController@clasificationStoreParam');
    // Route::get('/classification-parameter/{id}', 'Master\ClassificationController@showClassificationParameter');
    // Route::put('/classification-parameter/{id}', 'Master\ClassificationController@updateClassificationParameter');
    // Route::delete('/classification-parameter/{id}', 'Master\ClassificationController@deleteClassificationParameter');
    // Route::post('/multiple-delete-classification-param', 'Master\ClassificationController@multipleDeleteClassificationParam');

    // /* Location Type */
    // Route::get('/location-type', 'Master\LocationTypeController@index');
    // Route::get('/location-type-list', 'Master\LocationTypeController@list');
    // Route::post('/location-type', 'Master\LocationTypeController@store');
    // Route::get('/location-type/{id}', 'Master\LocationTypeController@show');
    // Route::get('/location-type-log/{id}', 'Master\LocationTypeController@log');
    // Route::post('/update-location-type/{id}', 'Master\LocationTypeController@update');
    // Route::delete('/location-type/{id}', 'Master\LocationTypeController@delete');
    // Route::post('/multiple-delete-location-type', 'Master\LocationTypeController@multipleDelete');

     // Settings
     Route::get('/setting', 'Master\SettingController@index');
     Route::get('/setting/{key}', 'Master\SettingController@show');
     Route::post('/setting/{key}', 'Master\SettingController@update');

     // Unit
    Route::get('/unit', 'Master\UnitController@index');
    Route::get('/unit-list-hash', 'Master\UnitController@list');
    Route::post('/unit', 'Master\UnitController@store');
    Route::get('/unit/{id}', 'Master\UnitController@show');
    Route::put('/unit/{id}', 'Master\UnitController@update');
    Route::delete('/unit/{id}', 'Master\UnitController@destroy');
    Route::post('/multiple-delete-unit', 'Master\UnitController@multipleDelete');

    // Ownership
    Route::get('/ownership', 'Master\OwnershipController@index');
    Route::get('/ownership-list-hash', 'Master\OwnershipController@list');
    Route::post('/ownership', 'Master\OwnershipController@store');
    Route::get('/ownership/{id}', 'Master\OwnershipController@show');
    Route::put('/ownership/{id}', 'Master\OwnershipController@update');
    Route::delete('/ownership/{id}', 'Master\OwnershipController@destroy');
    Route::post('/multiple-delete-ownership', 'Master\OwnershipController@multipleDelete');

    // Fund
    Route::get('/fund', 'Master\FundController@index');
    Route::get('/fund-list-hash', 'Master\FundController@list');
    Route::post('/fund', 'Master\FundController@store');
    Route::get('/fund/{id}', 'Master\FundController@show');
    Route::put('/fund/{id}', 'Master\FundController@update');
    Route::delete('/fund/{id}', 'Master\FundController@destroy');
    Route::post('/multiple-delete-fund', 'Master\FundController@multipleDelete');

    // Asset Category
    Route::get('/asset-category', 'Master\AssetCategoryController@index');
    Route::get('/asset-category-list-hash', 'Master\AssetCategoryController@list');
    Route::post('/asset-category', 'Master\AssetCategoryController@store');
    Route::get('/asset-category/{id}', 'Master\AssetCategoryController@show');
    Route::put('/asset-category/{id}', 'Master\AssetCategoryController@update');
    Route::delete('/asset-category/{id}', 'Master\AssetCategoryController@destroy');
    Route::post('/multiple-delete-asset-category', 'Master\AssetCategoryController@multipleDelete');


    // /** MASTER - RELEASE STRATEGY */
    // //Release Object
    // Route::get('/release-object', 'Master\ReleaseStrategy\ReleaseObjectController@index');
    // Route::get('/release-object-list', 'Master\ReleaseStrategy\ReleaseObjectController@list');
    // Route::post('/release-object', 'Master\ReleaseStrategy\ReleaseObjectController@store');
    // Route::get('/release-object/{id}', 'Master\ReleaseStrategy\ReleaseObjectController@show');
    // Route::put('/release-object/{id}', 'Master\ReleaseStrategy\ReleaseObjectController@update');
    // Route::delete('/release-object/{id}', 'Master\ReleaseStrategy\ReleaseObjectController@delete');
    // Route::post('/multiple-delete-release-object', 'Master\ReleaseStrategy\ReleaseObjectController@multipleDelete');

    // // Release Group
    // Route::get('/classification-for-workflow', 'Material\MaterialController@getClassificationForWorkflow');
    // Route::get('/release-group', 'Master\ReleaseStrategy\ReleaseGroupController@index');
    // Route::get('/release-group-list', 'Master\ReleaseStrategy\ReleaseGroupController@list');
    // Route::post('/release-group', 'Master\ReleaseStrategy\ReleaseGroupController@store');
    // Route::get('/release-group/{id}', 'Master\ReleaseStrategy\ReleaseGroupController@show');
    // Route::put('/release-group/{id}', 'Master\ReleaseStrategy\ReleaseGroupController@update');
    // Route::delete('/release-group/{id}', 'Master\ReleaseStrategy\ReleaseGroupController@delete');
    // Route::post('/multiple-delete-release-group', 'Master\ReleaseStrategy\ReleaseGroupController@multipleDelete');

    // // Release Code
    // Route::get('/release-code', 'Master\ReleaseStrategy\ReleaseCodeController@index');
    // Route::get('/release-code-list', 'Master\ReleaseStrategy\ReleaseCodeController@list');
    // Route::post('/release-code', 'Master\ReleaseStrategy\ReleaseCodeController@store');
    // Route::get('/release-code/{id}', 'Master\ReleaseStrategy\ReleaseCodeController@show');
    // Route::put('/release-code/{id}', 'Master\ReleaseStrategy\ReleaseCodeController@update');
    // Route::delete('/release-code/{id}', 'Master\ReleaseStrategy\ReleaseCodeController@delete');
    // Route::post('/multiple-delete-release-code', 'Master\ReleaseStrategy\ReleaseCodeController@multipleDelete');

    // // Release Code User
    // Route::get('/release-code-user', 'Master\ReleaseStrategy\ReleaseCodeUserController@index');
    // Route::get('/release-code-user-list', 'Master\ReleaseStrategy\ReleaseCodeUserController@list');
    // Route::post('/release-code-user', 'Master\ReleaseStrategy\ReleaseCodeUserController@store');
    // Route::get('/release-code-user/{id}', 'Master\ReleaseStrategy\ReleaseCodeUserController@show');
    // Route::put('/release-code-user/{id}', 'Master\ReleaseStrategy\ReleaseCodeUserController@update');
    // Route::delete('/release-code-user/{id}', 'Master\ReleaseStrategy\ReleaseCodeUserController@delete');
    // Route::post('/multiple-delete-release-code-user', 'Master\ReleaseStrategy\ReleaseCodeUserController@multipleDelete');

    // // Release strategy
    // Route::get('/release-strategy', 'Master\ReleaseStrategy\ReleaseStrategyController@index');
    // Route::get('/release-strategy-list', 'Master\ReleaseStrategy\ReleaseStrategyController@list');
    // Route::post('/release-strategy', 'Master\ReleaseStrategy\ReleaseStrategyController@store');
    // Route::get('/release-strategy/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@show');
    // Route::get('/release-strategy-activate/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@activate');
    // Route::get('/release-strategy-deactivate/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@deactivate');
    // Route::put('/release-strategy/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@update');
    // Route::delete('/release-strategy/{id}', 'Master\ReleaseStrategy\ReleaseStrategyController@delete');
    // Route::post('/multiple-delete-release-strategy', 'Master\ReleaseStrategy\ReleaseStrategyController@multipleDelete');

    // // release strategy assign release code
    // Route::post('/release-strategy/{id}/assign-code', 'Master\ReleaseStrategy\ReleaseStrategyController@assignCode');
    // Route::post('/release-strategy/{id}/add-release-code', 'Master\ReleaseStrategy\ReleaseStrategyController@addCode');
    // Route::post('/release-strategy/{id}/delete-release-code', 'Master\ReleaseStrategy\ReleaseStrategyController@deleteCode');

    // /** Release Strategy Classification */
    // Route::post('/release-strategy/{id}/classification', 'Master\ReleaseStrategy\ReleaseStrategyController@storeClassification');

    // /** Release Strategy Status */
    // Route::get('/release-strategy/{id}/status', 'Master\ReleaseStrategy\ReleaseStrategyController@status');
    // Route::post('/release-strategy/{id}/status', 'Master\ReleaseStrategy\ReleaseStrategyController@updateStatus');

    /** Setting */
    Route::get('/setting', 'Master\SettingController@index');
    Route::get('/setting/{key}', 'Master\SettingController@show');
    Route::post('/setting/{key}', 'Master\SettingController@update');


    // /** Location Type */
    // Route::get('/location-type', 'Master\LocationTypeController@index');
    // Route::get('/location-type-list', 'Master\LocationTypeController@list');
    // Route::post('/location-type', 'Master\LocationTypeController@store');
    // Route::get('/location-type/{id}', 'Master\LocationTypeController@show');
    // Route::get('/location-type-log/{id}', 'Master\LocationTypeController@log');
    // Route::post('/update-location-type/{id}', 'Master\LocationTypeController@update');
    // Route::delete('/location-type/{id}', 'Master\LocationTypeController@delete');
    // Route::post('/multiple-delete-location-type', 'Master\LocationTypeController@multipleDelete');

    // /** Plant */
    // Route::get('/plant', 'Master\PlantController@index');
    // Route::get('/plant-list', 'Master\PlantController@list');
    // Route::post('/plant', 'Master\PlantController@store');
    // Route::get('/plant/{id}', 'Master\PlantController@show');
    // Route::get('/plant-log/{id}', 'Master\PlantController@log');
    // Route::put('/plant/{id}', 'Master\PlantController@update');
    // Route::delete('/plant/{id}', 'Master\PlantController@delete');
    // Route::post('/multiple-delete-plant', 'Master\PlantController@multipleDelete');

    // /** Location */
    // Route::get('/company-list-by-type/{id}', 'Master\LocationController@getCompanyByType');
    // Route::get('/location', 'Master\LocationController@index');
    // Route::get('/location-list-hash', 'Master\LocationController@list');
    // Route::post('/location', 'Master\LocationController@store');
    // Route::get('/location/{id}', 'Master\LocationController@show');
    // Route::get('/location-log/{id}', 'Master\LocationController@log');
    // Route::put('/location/{id}', 'Master\LocationController@update');
    // Route::delete('/location/{id}', 'Master\LocationController@delete');
    // Route::post('/multiple-delete-location', 'Master\LocationController@multipleDelete');

    // Route::get('/location-list-by-plant/{id}', 'Master\LocationController@getLocationByPlant');
    // Route::get('/user-list-by-location/{id}', 'Master\LocationController@getUserByLocation');
    // Route::get('/location-list', 'Master\LocationController@getLocationList');

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


