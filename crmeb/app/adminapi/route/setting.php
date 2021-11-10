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
    Route::resource('admin', 'v1.setting.SystemAdmin')->except(['read'])->option(['real_name' => '管理员']);
    //退出登陆
    Route::get('admin/logout', 'v1.setting.SystemAdmin/logout')->name('SystemAdminLogout')->option(['real_name' => '退出登陆']);
    //修改状态
    Route::put('set_status/:id/:status', 'v1.setting.SystemAdmin/set_status')->name('SystemAdminSetStatus')->option(['real_name' => '修改管理员状态']);
    //获取当前管理员信息
    Route::get('info', 'v1.setting.SystemAdmin/info')->name('SystemAdminInfo')->option(['real_name' => '获取当前管理员信息']);
    //修改当前管理员信息
    Route::put('update_admin', 'v1.setting.SystemAdmin/update_admin')->name('SystemAdminUpdateAdmin')->option(['real_name' => '修改当前管理员信息']);
    //权限菜单资源路由
    Route::resource('menus', 'v1.setting.SystemMenus')->option(['real_name' => '权限菜单']);
    //未添加的权限规则列表
    Route::get('ruleList', 'v1.setting.SystemMenus/ruleList')->option(['real_name' => '未添加的权限规则列表']);
    //修改显示
    Route::put('menus/show/:id', 'v1.setting.SystemMenus/show')->name('SystemMenusShow')->option(['real_name' => '修改权限规格显示状态']);
    //身份列表
    Route::get('role', 'v1.setting.SystemRole/index')->option(['real_name' => '管理员身份列表']);
    //身份权限列表
    Route::get('role/create', 'v1.setting.SystemRole/create')->option(['real_name' => '管理员身份权限列表']);
    //编辑详情
    Route::get('role/:id/edit', 'v1.setting.SystemRole/edit')->option(['real_name' => '编辑管理员详情']);
    //保存新建或编辑
    Route::post('role/:id', 'v1.setting.SystemRole/save')->option(['real_name' => '新建或编辑管理员']);
    //修改身份状态
    Route::put('role/set_status/:id/:status', 'v1.setting.SystemRole/set_status')->option(['real_name' => '修改管理员身份状态']);
    //删除身份
    Route::delete('role/:id', 'v1.setting.SystemRole/delete')->option(['real_name' => '删除管理员身份']);
    //配置分类资源路由
    Route::resource('config_class', 'v1.setting.SystemConfigTab')->option(['real_name' => '系统配置分类']);
    //修改配置分类状态
    Route::put('config_class/set_status/:id/:status', 'v1.setting.SystemConfigTab/set_status')->option(['real_name' => '修改配置分类状态']);
    //配置资源路由
    Route::resource('config', 'v1.setting.SystemConfig')->option(['real_name' => '系统配置']);
    //修改配置状态
    Route::put('config/set_status/:id/:status', 'v1.setting.SystemConfig/set_status')->option(['real_name' => '修改配置状态']);
    //基本配置编辑表单
    Route::get('config/header_basics', 'v1.setting.SystemConfig/header_basics')->option(['real_name' => '基本配置编辑头部数据']);
    //基本配置编辑表单
    Route::get('config/edit_basics', 'v1.setting.SystemConfig/edit_basics')->option(['real_name' => '基本配置编辑表单']);
    //基本配置保存数据
    Route::post('config/save_basics', 'v1.setting.SystemConfig/save_basics')->option(['real_name' => '基本配置保存数据']);
    //基本配置上传文件
    Route::post('config/upload', 'v1.setting.SystemConfig/file_upload')->option(['real_name' => '基本配置上传文件']);
    //获取单个配置值
    Route::get('config/get_system/:name', 'v1.setting.SystemConfig/get_system')->option(['real_name' => '基本配置编辑表单']);
    //组合数据资源路由
    Route::resource('group', 'v1.setting.SystemGroup')->option(['real_name' => '组合数据']);
    //组合数据全部
    Route::get('group_all', 'v1.setting.SystemGroup/getGroup')->option(['real_name' => '组合数据全部']);
    //组合数据子数据资源路由
    Route::resource('group_data', 'v1.setting.SystemGroupData')->option(['real_name' => '组合数据子数据']);
    //修改数据状态
    Route::get('group_data/header', 'v1.setting.SystemGroupData/header')->option(['real_name' => '组合数据头部']);
    //修改数据状态
    Route::put('group_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status')->option(['real_name' => '修改组合数据状态']);
    //数据配置保存
    Route::post('group_data/save_all', 'v1.setting.SystemGroupData/saveAll')->option(['real_name' => '提交数据配置']);
    //获取城市数据列表
    Route::get('city/list/:parent_id', 'v1.setting.SystemCity/index')->option(['real_name' => '获取城市数据列表']);
    //添加城市数据表单
    Route::get('city/add/:parent_id', 'v1.setting.SystemCity/add')->option(['real_name' => '添加城市数据表单']);
    //修改城市数据表单
    Route::get('city/:id/edit', 'v1.setting.SystemCity/edit')->option(['real_name' => '修改城市数据表单']);
    //新增/修改城市数据
    Route::post('city/save', 'v1.setting.SystemCity/save')->option(['real_name' => '新增/修改城市数据']);
    //修改城市数据表单
    Route::delete('city/del/:city_id', 'v1.setting.SystemCity/delete')->option(['real_name' => '删除城市数据']);
    //清除城市数据缓存
    Route::get('city/clean_cache', 'v1.setting.SystemCity/clean_cache')->option(['real_name' => '清除城市数据缓存']);
    //运费模板列表
    Route::get('shipping_templates/list', 'v1.setting.ShippingTemplates/temp_list')->option(['real_name' => '运费模板列表']);
    //修改运费模板数据
    Route::get('shipping_templates/:id/edit', 'v1.setting.ShippingTemplates/edit')->option(['real_name' => '修改运费模板数据']);
    //保存新增修改
    Route::post('shipping_templates/save/:id', 'v1.setting.ShippingTemplates/save')->option(['real_name' => '新增或修改运费模版']);
    //删除运费模板
    Route::delete('shipping_templates/del/:id', 'v1.setting.ShippingTemplates/delete')->option(['real_name' => '删除运费模板']);
    //城市数据接口
    Route::get('shipping_templates/city_list', 'v1.setting.ShippingTemplates/city_list')->option(['real_name' => '城市数据接口']);
    //获取客服广告
    Route::get('get_kf_adv', 'v1.setting.SystemGroupData/getKfAdv')->option(['real_name' => '获取客服广告']);
    //设置客服广告
    Route::post('set_kf_adv', 'v1.setting.SystemGroupData/setKfAdv')->option(['real_name' => '设置客服广告']);


    //签到天数配置资源
    Route::resource('sign_data', 'v1.setting.SystemGroupData')->option(['real_name' => '签到天数配置']);
    //签到数据字段
    Route::get('sign_data/header', 'v1.setting.SystemGroupData/header')->option(['real_name' => '签到数据头部']);
    //修改签到数据状态
    Route::put('sign_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status')->option(['real_name' => '修改签到数据状态']);
    //订单详情动态图配置资源
    Route::resource('order_data', 'v1.setting.SystemGroupData')->option(['real_name' => '订单详情动态图配置资源']);
    //订单数据字段
    Route::get('order_data/header', 'v1.setting.SystemGroupData/header')->option(['real_name' => '订单数据字段']);
    //订单数据状态
    Route::put('order_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status')->option(['real_name' => '订单数据状态']);
    //个人中心菜单配置资源
    Route::resource('usermenu_data', 'v1.setting.SystemGroupData')->option(['real_name' => '个人中心菜单']);
    //个人中心菜单数据字段
    Route::get('usermenu_data/header', 'v1.setting.SystemGroupData/header')->option(['real_name' => '个人中心菜单数据字段']);
    //个人中心菜单数据状态
    Route::put('usermenu_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status')->option(['real_name' => '个人中心菜单数据状态']);
    //分享海报配置资源
    Route::resource('poster_data', 'v1.setting.SystemGroupData')->option(['real_name' => '分享海报']);
    //分享海报数据字段
    Route::get('poster_data/header', 'v1.setting.SystemGroupData/header')->option(['real_name' => '分享海报数据字段']);
    //分享海报数据状态
    Route::put('poster_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status')->option(['real_name' => '分享海报数据状态']);
    //秒杀配置资源
    Route::resource('seckill_data', 'v1.setting.SystemGroupData')->option(['real_name' => '秒杀配置']);
    //秒杀数据字段
    Route::get('seckill_data/header', 'v1.setting.SystemGroupData/header')->option(['real_name' => '秒杀数据字段']);
    //秒杀数据状态
    Route::put('seckill_data/set_status/:id/:status', 'v1.setting.SystemGroupData/set_status')->option(['real_name' => '秒杀数据状态']);
    //获取隐私协议
    Route::get('get_user_agreement', 'v1.setting.SystemGroupData/getUserAgreement')->option(['real_name' => '获取隐私协议']);
    //设置隐私协议
    Route::post('set_user_agreement', 'v1.setting.SystemGroupData/setUserAgreement')->option(['real_name' => '设置隐私协议']);

    //系统通知
    //系统通知列表
    Route::get('notification/index', 'v1.setting.SystemNotification/index')->option(['real_name' => '系统通知列表']);
    //获取单条数据
    Route::get('notification/info', 'v1.setting.SystemNotification/info')->option(['real_name' => '获取单条通知数据']);
    //保存通知设置
    Route::post('notification/save', 'v1.setting.SystemNotification/save')->option(['real_name' => '保存通知设置']);
    //修改消息状态
    Route::put('notification/set_status/:type/:status/:id', 'v1.setting.SystemNotification/set_status')->option(['real_name' => '修改消息状态']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
