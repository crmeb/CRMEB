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
    //赠送会员等级
    Route::get('give_level/:id', 'v1.user.user/give_level');
    //执行赠送会员等级
    Route::put('save_give_level/:id', 'v1.user.user/save_give_level');
    //赠送付费会员时长
    Route::get('give_level_time/:id', 'v1.user.user/give_level_time');
    //执行赠送付费会员时长
    Route::put('save_give_level_time/:id', 'v1.user.user/save_give_level_time');
    //清除会员等级
    Route::delete('del_level/:id', 'v1.user.user/del_level');
    //编辑其他
    Route::get('edit_other/:id', 'v1.user.user/edit_other');
    //编辑其他
    Route::put('update_other/:id', 'v1.user.user/update_other');
    //修改用户状态
    Route::put('set_status/:status/:id', 'v1.user.user/set_status');
    //获取指定用户的信息
    Route::get('one_info/:id', 'v1.user.user/oneUserInfo');
    /*会员设置模块*/
    //获取添加会员等级表单
    Route::get('user_level/create', 'v1.user.UserLevel/create');
    //添加或修改会员等级
    Route::post('user_level', 'v1.user.UserLevel/save');
    //等级详情
    Route::get('user_level/read/:id', 'v1.user.UserLevel/read');
    //获取系统设置的vip列表
    Route::get('user_level/vip_list', 'v1.user.UserLevel/get_system_vip_list');
    //删除会员等级
    Route::put('user_level/delete/:id', 'v1.user.UserLevel/delete');
    //设置单个商品上架|下架
    Route::put('user_level/set_show/:id/:is_show', 'v1.user.UserLevel/set_show');
    //等级列表快速编辑
    Route::put('user_level/set_value/:id', 'v1.user.UserLevel/set_value');
    //等级任务列表
    Route::get('user_level/task/:level_id', 'v1.user.UserLevel/get_task_list');
    //快速编辑等级任务
    Route::put('user_level/set_task/:id', 'v1.user.UserLevel/set_task_value');
    //设置等级任务显示|隐藏
    Route::put('user_level/set_task_show/:id/:is_show', 'v1.user.UserLevel/set_task_show');
    //设置是否务必达成
    Route::put('user_level/set_task_must/:id/:is_must', 'v1.user.UserLevel/set_task_must');
    //添加等级任务表单
    Route::get('user_level/create_task', 'v1.user.UserLevel/create_task');
    //保存或者修改任务
    Route::post('user_level/save_task', 'v1.user.UserLevel/save_task');
    //删除任务
    Route::delete('user_level/delete_task/:id', 'v1.user.UserLevel/delete_task');
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

    //会员卡批次列表资源
    Route::get('member_batch/index', 'v1.user.member.MemberCardBatch/index');
    //添加会员卡批次
    Route::post('member_batch/save/:id', 'v1.user.member.MemberCardBatch/save');
    //会员卡列表
    Route::get('member_card/index/:card_batch_id', 'v1.user.member.MemberCard/index');
    //会员卡修改状态
    Route::get('member_card/set_status', 'v1.user.member.MemberCard/set_status');
    //列表单字段修改操作
    Route::get('member_batch/set_value/:id', 'v1.user.member.MemberCardBatch/set_value');
    //会员类型
    Route::get('member/ship', 'v1.user.member.MemberCard/member_ship');
    //会员类型修改状态
    Route::get('member_ship/set_ship_status', 'v1.user.member.MemberCard/set_ship_status');
    //会员卡类型编辑
    Route::post('member_ship/save/:id', 'v1.user.member.MemberCard/ship_save');
    //兑换会员卡二维码
    Route::get('member_scan', 'v1.user.member.MemberCardBatch/member_scan');
    //会员记录
    Route::get('member/record', 'v1.user.member.MemberCard/member_record');
    //会员权益
    Route::get('member/right', 'v1.user.member.MemberCard/member_right');
    //会员权益修改
    Route::post('member_right/save/:id', 'v1.user.member.MemberCard/right_save');
    //会员协议
    Route::post('member_agreement/save/:id', 'v1.user.member.MemberCardBatch/save_member_agreement');
    //获取会员协议
    Route::get('member/agreement', 'v1.user.member.MemberCardBatch/getAgreement');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
