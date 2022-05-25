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

    /* Classification  */
    Route::get('/classification', 'Master\ClassificationController@index');
    Route::get('/classification-list', 'Master\ClassificationController@list');
    Route::post('/classification', 'Master\ClassificationController@store');
    Route::get('/classification/{id}', 'Master\ClassificationController@show');
    Route::get('/classification-log/{id}', 'Master\ClassificationController@log');
    Route::put('/classification/{id}', 'Master\ClassificationController@update');
    Route::delete('/classification/{id}', 'Master\ClassificationController@delete');
    Route::post('/multiple-delete-classification', 'Master\ClassificationController@multipleDelete');

    /* Classification Parameter */
    Route::post('/classification/{id}/addparam', 'Master\ClassificationController@clasificationStoreParam');
    Route::get('/classification-parameter/{id}', 'Master\ClassificationController@showClassificationParameter');
    Route::put('/classification-parameter/{id}', 'Master\ClassificationController@updateClassificationParameter');
    Route::delete('/classification-parameter/{id}', 'Master\ClassificationController@deleteClassificationParameter');
    Route::post('/multiple-delete-classification-param', 'Master\ClassificationController@multipleDeleteClassificationParam');

    /* Material  */
    Route::get('/material', 'Master\MaterialController@index');
    Route::get('/material-list', 'Master\MaterialController@list');
    Route::post('/material', 'Master\MaterialController@store');
    Route::get('/material/{id}', 'Master\MaterialController@show');
    Route::put('/material/{id}', 'Master\MaterialController@update');
    Route::delete('/material/{id}', 'Master\MaterialController@delete');
    Route::post('/multiple-delete-material', 'Master\MaterialController@multipleDelete');
    Route::post('/material/list-image/{material_id}', 'Master\MaterialController@listImage');
    Route::post('/material/upload-image/{material_id}', 'Master\MaterialController@uploadImage');
    Route::post('/material/delete-image/{id}', 'Master\MaterialController@deleteImage');

    /* Material Sourcing */
    Route::get('/material-sourcing', 'Master\MaterialSourcingController@index');
    Route::get('/material-sourcing-list', 'Master\MaterialSourcingController@list');
    Route::post('/material-sourcing', 'Master\MaterialSourcingController@updateOrCreate');
    Route::get('/material-sourcing/{id}', 'Master\MaterialSourcingController@show');
    Route::get('/material-sourcing/{room_id}/room', 'Master\MaterialSourcingController@showMaterialByRoom');
    Route::post('/multiple-delete-material-sourcing', 'Master\MaterialSourcingController@multipleDelete');

    // Settings
    Route::get('/setting', 'Master\SettingController@index');
    Route::get('/setting/{key}', 'Master\SettingController@show');
    Route::post('/setting/{key}', 'Master\SettingController@update');

     // Uom
    Route::get('/uom', 'Master\UnitOfMeasurementController@index');
    Route::get('/uom-list', 'Master\UnitOfMeasurementController@list');
    Route::post('/uom', 'Master\UnitOfMeasurementController@store');
    Route::get('/uom/{id}', 'Master\UnitOfMeasurementController@show');
    Route::put('/uom/{id}', 'Master\UnitOfMeasurementController@update');
    Route::delete('/uom/{id}', 'Master\UnitOfMeasurementController@destroy');
    Route::post('/multiple-delete-uom', 'Master\UnitOfMeasurementController@multipleDelete');

    /** Setting */
    Route::get('/setting', 'Master\SettingController@index');
    Route::get('/setting/{key}', 'Master\SettingController@show');
    Route::post('/setting/{key}', 'Master\SettingController@update');

    /** Plant */
    Route::get('/plant', 'Master\PlantController@index');
    Route::get('/plant-list', 'Master\PlantController@list');
    Route::post('/plant', 'Master\PlantController@store');
    Route::get('/plant/{id}', 'Master\PlantController@show');
    Route::get('/plant-log/{id}', 'Master\PlantController@log');
    Route::put('/plant/{id}', 'Master\PlantController@update');
    Route::delete('/plant/{id}', 'Master\PlantController@delete');
    Route::post('/multiple-delete-plant', 'Master\PlantController@multipleDelete');

    /** Vendor */
    Route::get('/vendor', 'Master\VendorController@index');
    Route::get('/vendor-list', 'Master\VendorController@list');
    Route::post('/vendor', 'Master\VendorController@store');
    Route::get('/vendor/{id}', 'Master\VendorController@show');
    Route::get('/vendor-log/{id}', 'Master\VendorController@log');
    Route::put('/vendor/{id}', 'Master\VendorController@update');
    Route::delete('/vendor/{id}', 'Master\VendorController@delete');
    Route::post('/multiple-delete-vendor', 'Master\VendorController@multipleDelete');

    /** Plant */
    Route::get('/room', 'Master\RoomController@index');
    Route::get('/room-list', 'Master\RoomController@list');
    Route::post('/room', 'Master\RoomController@store');
    Route::get('/room/{id}', 'Master\RoomController@show');
    Route::get('/room-log/{id}', 'Master\RoomController@log');
    Route::put('/room/{id}', 'Master\RoomController@update');
    Route::delete('/room/{id}', 'Master\RoomController@delete');
    Route::post('/multiple-delete-room', 'Master\RoomController@multipleDelete');
});

Route::group(['middleware' => ['auth:api', 'activity'], 'prefix' => 'transaction', 'namespace' => 'Api'], function () {
    /* Stock */
    Route::get('/stock', 'StockController@index');
    Route::get('/stock-list', 'StockController@list');
    Route::get('/stock/{id}', 'StockController@show');
    Route::post('/stock-export', 'StockController@export');
    /* Stock History*/
    Route::get('/stock-history', 'StockHistoryController@index');
    Route::get('/stock-history-list', 'StockHistoryController@list');
    Route::get('/stock-history/{stock_id}/stock', 'StockHistoryController@stockHistoryByStockId');
    Route::get('/stock-history/{stock_id}/serial-number', 'StockHistoryController@getStockHistorySerialNumberByStockId');
});

// Authenticated User API (Dashboard)
Route::group(['middleware' => ['auth:api', 'activity'], 'prefix' => 'user', 'namespace' => 'Api'], function () {
    Route::get('/profile', 'ProfileController@profile');
    Route::post('/update-login-detail', 'ProfileController@updateLoginDetail');
    Route::post('/update-profile', 'ProfileController@updateProfile');
    Route::get('/login-history', 'ProfileController@loginHistory');
    // Route::post('/change-photo-profile', 'ProfileController@changePhotoProfile');
    Route::post('/change-photo-profile/{id}', 'ProfileController@changePhotoProfile');
    Route::post('/delete-photo-profile/{id}', 'ProfileController@deletePhotoProfile');
    Route::post('/change-password', 'ProfileController@changePassword');
    Route::get('/module-object-list', 'ProfileController@moduleList');
});


