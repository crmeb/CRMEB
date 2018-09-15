<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use \think\Route;
//兼容模式 不支持伪静态可开启
//\think\Url::root('index.php?s=');
Route::group('admin',function(){
//    Route::rule('/index2','admin/Index/index2','get');
//    Route::controller('index','admin/Index');
//    resource('system_menus','SystemMenus');
//    Route::rule('/menus','SystemMenus','get');
//    Route::resource('menus','admin/SystemMenus',['var'=>['menus'=>'menu_id']]);
//    Route::miss(function(){
//        return '页面不存在!';
//    });
});

