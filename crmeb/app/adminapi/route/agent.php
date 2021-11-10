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
 * 分销管理 相关路由
 */
Route::group('agent', function () {
    //推销员列表
    Route::get('index', 'v1.agent.AgentManage/index')->option(['real_name' => '分销员列表']);
    //修改上级推广人
    Route::put('spread', 'v1.agent.AgentManage/editSpread')->option(['real_name' => '修改上级推广人']);
    //头部统计
    Route::get('statistics', 'v1.agent.AgentManage/get_badge')->option(['real_name' => '分销员列表头部统计']);
    //推广人列表
    Route::get('stair', 'v1.agent.AgentManage/get_stair_list')->option(['real_name' => '推广人列表']);
    //推广人头部统计
//    Route::get('stair/statistics', 'v1.agent.AgentManage/get_stair_badge')->option(['real_name' => '推广人头部统计']);
    //统计推广订单列表
    Route::get('stair/order', 'v1.agent.AgentManage/get_stair_order_list')->option(['real_name' => '推广订单列表']);
    //统计推广订单列表头部
//    Route::get('stair/order/statistics', 'v1.agent.AgentManage/get_stair_order_badge')->option(['real_name' => '推广订单列表头部']);
    //清除上级推广人
    Route::put('stair/delete_spread/:uid', 'v1.agent.AgentManage/delete_spread')->option(['real_name' => '清除上级推广人']);
    //取消推广资格
    Route::put('stair/delete_system_spread/:uid', 'v1.agent.AgentManage/delete_system_spread')->option(['real_name' => '取消推广资格']);
    //查看公众号推广二维码
    Route::get('look_code', 'v1.agent.AgentManage/look_code')->option(['real_name' => '查看公众号推广二维码']);
    //查看小程序推广二维码
    Route::get('look_xcx_code', 'v1.agent.AgentManage/look_xcx_code')->option(['real_name' => '查看小程序推广二维码']);
    //查看H5推广二维码
    Route::get('look_h5_code', 'v1.agent.AgentManage/look_h5_code')->option(['real_name' => '查看H5推广二维码']);
    //积分配置编辑表单
    Route::get('config/edit_basics', 'v1.setting.SystemConfig/edit_basics')->option(['real_name' => '积分配置编辑表单']);
    //积分配置保存数据
    Route::post('config/save_basics', 'v1.setting.SystemConfig/save_basics')->option(['real_name' => '积分配置保存数据']);

    //分销员等级资源路由
    Route::resource('level', 'v1.agent.AgentLevel')->name('AgentLevelResource')->option(['real_name' => '分销员等级']);
    //修改分销等级状态
    Route::put('level/set_status/:id/:status', 'v1.agent.AgentLevel/set_status')->name('levelSetStatus')->option(['real_name' => '修改分销等级状态']);
    //分销员等级任务资源路由
    Route::resource('level_task', 'v1.agent.AgentLevelTask')->name('AgentLevelTaskResource')->option(['real_name' => '分销员等级任务']);
    //修改分销任务状态
    Route::put('level_task/set_status/:id/:status', 'v1.agent.AgentLevelTask/set_status')->name('levelTaskSetStatus')->option(['real_name' => '修改分销等级任务状态']);
    //获取赠送分销等级表单
    Route::get('get_level_form', 'v1.agent.AgentManage/getLevelForm')->name('getLevelForm')->option(['real_name' => '获取赠送分销等级表单']);
    //赠送分销等级
    Route::post('give_level', 'v1.agent.AgentManage/giveAgentLevel')->name('giveAgentLevel')->option(['real_name' => '赠送分销等级']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
