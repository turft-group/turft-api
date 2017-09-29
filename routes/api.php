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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register','Auth\RegisterController@register');

Route::get('user','API\UserController@view')->middleware('auth:api');

Route::get('/group', 'API\GroupController@index')->middleware(['auth:api', 'role:admin']);
Route::post('/group', 'API\GroupController@store')->middleware('auth:api');
Route::get('/group/{group}', 'API\GroupController@show')->middleware('auth:api');
Route::put('/group/{group}', 'API\GroupController@update')->middleware('auth:api');
Route::delete('/group/{group}', 'API\GroupController@destroy')->middleware('auth:api');
Route::post('/group/{group}/user', 'API\GroupController@addUser')->middleware('auth:api');