<?php

use Versatile\Pages\Page;
use Illuminate\Support\Facades\Request;

$accountController = '\Versatile\Front\Http\Controllers\AccountController';
$searchController = '\Versatile\Front\Http\Controllers\SearchController';

/**
 * Authentication
 */
Route::group(['middleware' => ['web']], function () use ($accountController) {
    Route::group(['namespace' => 'App\Http\Controllers'], function () {
        Auth::routes();
    });

    Route::group(['middleware' => 'auth', 'as' => 'versatile-frontend.account'], function () use ($accountController) {
        Route::get('/account', "$accountController@index");
        Route::post('/account', "$accountController@updateAccount");
    });
});

/**
 * Let's get some search going
 */
Route::get('/search', "$searchController@index")
    ->middleware(['web'])
    ->name('versatile-frontend.search');
