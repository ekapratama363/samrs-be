<?php

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

Route::get('/', function () {
    return 'Connected';
});

// Auth::routes();
// Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'terminal'], function () {
    Route::get('/composer-install', function () {
        exec('composer install');
    });

    Route::get('/dbseed', function () {
        \Artisan::call('db:seed');
        dd("Db is seedered");
    });
    
    Route::get('/migrate', function () {
        \Artisan::call('migrate');
        dd("Db is migrated");
    });
    
    Route::get('/migrate-rollback', function () {
        \Artisan::call('migrate:rollback');
        dd("Db is rollbacked");
    });
    
    Route::get('/migrate-fresh', function () {
        \Artisan::call('migrate:fresh');
        dd("Db is rollbacked");
    });
    
    Route::get('/storage-link', function () {
        \Artisan::call('storage:link');
        dd("Storage is linked");
    });

    Route::get('/cache-clear', function () {
        \Artisan::call('cache:clear');
        dd("cache clear");
    });

    Route::get('/route-clear', function () {
        \Artisan::call('route:clear');
        dd("route clear");
    });

    Route::get('/view-clear', function () {
        \Artisan::call('view:clear');
        dd("view clear");
    });

    Route::get('/config-cache', function () {
        \Artisan::call('config:cache');
        dd("config cache");
    });
});