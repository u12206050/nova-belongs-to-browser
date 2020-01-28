<?php

use Illuminate\Support\Facades\Route;

Route::post('/load/{resource}', 'ResourceController@load');
Route::post('/fetch/{resource}', 'ResourceController@fetch');