<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;

/**
 * 系统设置维护 系统权限管理、系统菜单管理 系统配置 相关路由
 */
Route::group('setting', function () {

    //管理员资源路由
    Route::resource('admin', 'v1.setting.SystemAdmin')->except(['read']);
    //退出登陆
    Route::get('admin/logout', 'v1.setting.SystemAdmin/logout')->name('SystemAdminLogout');
    //修改状态
    Route::put('set_status/:id/:status', 'v1.setting.SystemAdmin/set_status')->name('SystemAdminSetStatus');
    //获取当前管理员信息
    Route::get('info', 'v1.setting.SystemAdmin/info')->name('SystemAdminInfo');
    //修改当前管理员信息
    Route::put('update_admin', 'v1.setting.SystemAdmin/update_admin')->name('SystemAdminUpdateAdmin');
    //权限菜单资源路由
    Route::resource('menus', 'v1.setting.SystemMenus');
    //修改显示
    Route::put('menus/show/:id', 'v1.setting.SystemMenus/show')->name('SystemMenusShow');
    //身份列表
    Route::get('role', 'v1.setting.SystemRole/index');
    //身份权限列表
    Route::get('role/create', 'v1.setting.SystemRole/create');
    //编辑详情
    Route::get('role/:id/edit', 'v1.setting.SystemRole/edit');
    //保存新建或编辑
    Route::post('role/:id', 'v1.setting.SystemRole/save');
    //修改身份状态
    Route::put('role/set_status/:id/:status', 'v1.setting.SystemRole/set_status');
    //删除身份
    Route::delete('role/:id', 'v1.setting.SystemRole/delete');
    //配置分类资源路由
    Route::resource('config_class', 'v1.setting.SystemConfigTab');
    //修改配置分类状态
    Route::put('config_class/set_status/:id/:status', 'v1.setting.SystemConfigTab/set_status');
    //配置资源路由
    Route::resource('config', 'v1.setting.SystemConfig');
    //修改配置状态
    Route::put('config/set_status/:id/:status', 'v1.setting.SystemConfig/set_status');
    //基本配置编辑表单
    Route::get('config/header_basics', 'v1.setting.SystemConfig/header_basics');
    //基本配置编辑表单
    Route::get('config/edit_basics', 'v1.setting.SystemConfig/edit_basics');
    //基本配置保存数据
    Route::post('config/save_basics', 'v1.setting.SystemConfig/save_basics');
    //基本配置上传文件
    Route::post('config/upload', 'v1.setting.SystemConfig/file_upload');
    //组合数据资源路由
    Route::resource('group', 'v1.setting.SystemGroup');
    //组合数据全部
    Route::get('group_all', 'v1.setting.SystemGroup/getGroup');
    //组合数据子数据资源路由
    Route::resource('group_data', 'v1.setting.SystemGroupData');
    //修改数据状态
    Route::get('group_data/header', 'v1.setting.SystemGroupData/header');
    //修改数据状态
    Route::put('group_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status');
    //获取城市数据列表
    Route::get('city/list/:parent_id', 'v1.setting.SystemCity/index');
    //添加城市数据表单
    Route::get('city/add/:parent_id', 'v1.setting.SystemCity/add');
    //修改城市数据表单
    Route::get('city/:id/edit', 'v1.setting.SystemCity/edit');
    //新增/修改城市数据
    Route::post('city/save', 'v1.setting.SystemCity/save');
    //修改城市数据表单
    Route::delete('city/del/:city_id', 'v1.setting.SystemCity/delete');
    //清除城市数据缓存
    Route::get('city/clean_cache', 'v1.setting.SystemCity/clean_cache');
    //运费模板列表
    Route::get('shipping_templates/list', 'v1.setting.ShippingTemplates/temp_list');
    //修改运费模板数据
    Route::get('shipping_templates/:id/edit', 'v1.setting.ShippingTemplates/edit');
    //保存新增修改
    Route::post('shipping_templates/save/:id', 'v1.setting.ShippingTemplates/save');
    //删除运费模板
    Route::delete('shipping_templates/del/:id', 'v1.setting.ShippingTemplates/delete');
    //城市数据接口
    Route::get('shipping_templates/city_list', 'v1.setting.ShippingTemplates/city_list');

    //订单详情动态图配置资源
    Route::resource('order_data', 'v1.setting.SystemGroupData');
    //订单数据字段
    Route::get('order_data/header', 'v1.setting.SystemGroupData/header');
    //订单数据状态
    Route::put('order_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status');
    //个人中心菜单配置资源
    Route::resource('usermenu_data', 'v1.setting.SystemGroupData');
    //个人中心菜单数据字段
    Route::get('usermenu_data/header', 'v1.setting.SystemGroupData/header');
    //个人中心菜单数据状态
    Route::put('usermenu_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
