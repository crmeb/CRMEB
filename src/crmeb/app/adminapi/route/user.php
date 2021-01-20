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
 * 用户模块 相关路由
 */
Route::group('user', function () {
    //用户管理资源路由
    Route::resource('user', 'v1.user.user');
    //添加用户保存
    Route::post('user/save', 'v1.user.user/save_info');
    //同步微信用户
    Route::get('user/syncUsers', 'v1.user.user/syncWechatUsers');
    //用户表单头
    Route::get('user/type_header', 'v1.user.user/type_header');
    //编辑其他
    Route::get('edit_other/:id', 'v1.user.user/edit_other');
    //编辑其他
    Route::put('update_other/:id', 'v1.user.user/update_other');
    //修改用户状态
    Route::put('set_status/:status/:id', 'v1.user.user/set_status');
    //获取指定用户的信息
    Route::get('one_info/:id', 'v1.user.user/oneUserInfo');
    //获取用户分组列表
    Route::get('user_group/list', 'v1.user.UserGroup/index');
    //添加修改分组表单
    Route::get('user_group/add/:id', 'v1.user.UserGroup/add');
    //保存分组表单数据
    Route::post('user_group/save', 'v1.user.UserGroup/save');
    //删除分组数据
    Route::delete('user_group/del/:id', 'v1.user.UserGroup/delete');
    //设置会员分组
    Route::post('set_group', 'v1.user.user/set_group');
    //执行设置会员分组
    Route::put('save_set_group', 'v1.user.user/save_set_group');
    //会员标签列表
    Route::get('user_label', 'v1.user.UserLabel/index');
    //会员标签添加修改表单
    Route::get('user_label/add/:id', 'v1.user.UserLabel/add');
    //保存标签表单数据
    Route::post('user_label/save', 'v1.user.UserLabel/save');
    //删除会员标签
    Route::delete('user_label/del/:id', 'v1.user.UserLabel/delete');
    //设置会员分组
    Route::post('set_label', 'v1.user.user/set_label');
    //获取用户标签
    Route::get('label/:uid', 'v1.user.UserLabel/getUserLabel');
    //设置和取消用户标签
    Route::post('label/:uid', 'v1.user.UserLabel/setUserLabel');
    //设置会员分组
    Route::put('save_set_label', 'v1.user.user/save_set_label');
    //标签分类
    Route::resource('user_label_cate', 'v1.user.UserLabelCate');
    Route::get('user_label_cate/all', 'v1.user.user_label_cate/getAll');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
