<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/timestamp/in', 'TimestampController@in');
Route::post('/timestamp/out', 'TimestampController@out');
Route::put('/timestamp/id/{id}', 'TimestampController@edit');
Route::delete('/timestamp/id/{id}', 'TimestampController@delete');
