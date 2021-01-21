<?php

namespace app\swagger\route;

use think\facade\Route;


Route::get('api', 'Index/getApiContent')->append(['api_name' => 'api']);
Route::get('adminapi', 'Index/getApiContent')->append(['api_name' => 'adminapi']);
Route::get('index', 'Index/index');
Route::get('/', 'Index/index');

