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
 * 应用模块 相关路由
 */
Route::group('app', function () {
    //小程序模板资源路由
    Route::resource('routine', 'v1.application.routine.RoutineTemplate')->name('RoutineResource');
    //客服反馈接口
    Route::resource('feedback', 'v1.application.wechat.StoreServiceFeedback')->only(['index', 'delete', 'update', 'edit']);
    //一键同步订阅消息
    Route::get('routine/syncSubscribe', 'v1.application.routine.RoutineTemplate/syncSubscribe')->name('syncSubscribe');
    //修改状态
    Route::put('routine/set_status/:id/:status', 'v1.application.routine.RoutineTemplate/set_status')->name('RoutineSetStatus');
    //菜单值
    Route::get('wechat/menu', 'v1.application.wechat.menus/index');
    //保存菜单
    Route::post('wechat/menu', 'v1.application.wechat.menus/save');
    //微信模板消息资源路由
    Route::resource('wechat/template', 'v1.application.wechat.WechatTemplate')->name('WechatTemplateResource');

    //微信模板消息修改状态
    Route::put('wechat/template/set_status/:id/:status', 'v1.application.wechat.WechatTemplate/set_status')->name('WechatTemplateSetStatus');
    //关注回复
    Route::get('wechat/reply', 'v1.application.wechat.Reply/reply');
    //获取关注回复二维码
    Route::get('wechat/code_reply/:id', 'v1.application.wechat.Reply/code_reply');
    //关键字回复列表
    Route::get('wechat/keyword', 'v1.application.wechat.Reply/index');
    //关键字详情
    Route::get('wechat/keyword/:id', 'v1.application.wechat.Reply/read');
    //保存关键字修改
    Route::post('wechat/keyword/:id', 'v1.application.wechat.Reply/save');
    //删除关键字
    Route::delete('wechat/keyword/:id', 'v1.application.wechat.Reply/delete');
    //修改关键字状态
    Route::put('wechat/keyword/set_status/:id/:status', 'v1.application.wechat.Reply/set_status');
    //图文列表
    Route::get('wechat/news', 'v1.application.wechat.WechatNewsCategory/index');
    //详情
    Route::get('wechat/news/:id', 'v1.application.wechat.WechatNewsCategory/read');
    //保存图文
    Route::post('wechat/news', 'v1.application.wechat.WechatNewsCategory/save');
    //删除图文
    Route::delete('wechat/news/:id', 'v1.application.wechat.WechatNewsCategory/delete');
    //发送图文消息
    Route::post('wechat/push', 'v1.application.wechat.WechatNewsCategory/push');
    /*微信用户管理*/
    //用户列表
    Route::get('wechat/user', 'v1.application.wechat.WechatUser/index');
    //获取用户分组和标签
    Route::get('wechat/user/tag_group', 'v1.application.wechat.WechatUser/get_tag_group');
    //修改用户标签表单
    Route::get('wechat/user_tag/:openid/edit', 'v1.application.wechat.WechatUser/edit_user_tag');
    //修改用户标签
    Route::put('wechat/user_tag/:openid', 'v1.application.wechat.WechatUser/update_user_tag');
    //修改用户分组表单
    Route::get('wechat/user_group/:openid/edit', 'v1.application.wechat.WechatUser/edit_user_group');
    //修改用户分组
    Route::put('wechat/user_group/:openid', 'v1.application.wechat.WechatUser/update_user_group');
    //同步标签
    Route::put('wechat/syn_tag/:openid', 'v1.application.wechat.WechatUser/syn_tag');
    //标签列表
    Route::get('wechat/tag', 'v1.application.wechat.WechatUser/tag');
    //新增标签表单
    Route::get('wechat/tag/create', 'v1.application.wechat.WechatUser/create_tag');
    //新增标签
    Route::post('wechat/tag', 'v1.application.wechat.WechatUser/save_tag');
    //编辑标签表单
    Route::get('wechat/tag/:id/edit', 'v1.application.wechat.WechatUser/edit_tag');
    //编辑标签
    Route::put('wechat/tag/:id', 'v1.application.wechat.WechatUser/update_tag');
    //删除标签
    Route::delete('wechat/tag/:id', 'v1.application.wechat.WechatUser/delete_tag');
    //分组列表
    Route::get('wechat/group', 'v1.application.wechat.WechatUser/group');
    //新增分组表单
    Route::get('wechat/group/create', 'v1.application.wechat.WechatUser/create_group');
    //新增分组
    Route::post('wechat/group', 'v1.application.wechat.WechatUser/save_group');
    //编辑分组表单
    Route::get('wechat/group/:id/edit', 'v1.application.wechat.WechatUser/edit_group');
    //编辑分组
    Route::put('wechat/group/:id', 'v1.application.wechat.WechatUser/update_group');
    //删除分组
    Route::delete('wechat/group/:id', 'v1.application.wechat.WechatUser/delete_group');
    //用户行为列表
    Route::get('wechat/action', 'v1.application.wechat.WechatMessage/index');
    //用户行为列表操作名称列表
    Route::get('wechat/action/operate', 'v1.application.wechat.WechatMessage/operate');

    //客服列表
    Route::get('wechat/kefu', 'v1.application.wechat.StoreService/index');
    //客服登录
    Route::get('wechat/kefu/login/:id', 'v1.application.wechat.StoreService/keufLogin');
    //新增客服选择用户列表
    Route::get('wechat/kefu/create', 'v1.application.wechat.StoreService/create');
    //新增客服表单
    Route::get('wechat/kefu/add', 'v1.application.wechat.StoreService/add');
    //保存新建的数据
    Route::post('wechat/kefu', 'v1.application.wechat.StoreService/save');
    //编辑客服表单
    Route::get('wechat/kefu/:id/edit', 'v1.application.wechat.StoreService/edit');
    //保存编辑的数据
    Route::put('wechat/kefu/:id', 'v1.application.wechat.StoreService/update');
    //删除
    Route::delete('wechat/kefu/:id', 'v1.application.wechat.StoreService/delete');
    //修改状态
    Route::put('wechat/kefu/set_status/:id/:status', 'v1.application.wechat.StoreService/set_status');
    //聊天记录
    Route::get('wechat/kefu/record/:id', 'v1.application.wechat.StoreService/chat_user');
    //查看对话
    Route::get('wechat/kefu/chat_list', 'v1.application.wechat.StoreService/chat_list');

    //话术接口
    Route::resource('wechat/speechcraft', 'v1.application.wechat.StoreServiceSpeechcraft');
    //话术分类接口
    Route::resource('wechat/speechcraftcate', 'v1.application.wechat.StoreServiceSpeechcraftCate');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
