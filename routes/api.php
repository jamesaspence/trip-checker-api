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

Route::prefix('v1')->name('api.')->group(function () {
    Route::post('/login', 'CredentialController@login')->name('login');
    Route::post('/register', 'CredentialController@registerUser')->name('register');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::delete('/logout', 'CredentialController@logout')->name('logout');

        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', 'TemplateController@getTemplates')
                ->name('getTemplates');
            Route::post('/', 'TemplateController@createTemplate')
                ->name('create');
            Route::get('/{template}', 'TemplateController@getTemplate')
                ->name('getTemplate');
            Route::put('/{template}', 'TemplateController@editTemplate')
                ->name('update');
            Route::delete('/{template}', 'TemplateController@deleteTemplate')
                ->name('delete');
        });
    });
});