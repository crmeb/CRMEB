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
    Route::get('index', 'v1.agent.AgentManage/index');
    //修改上级推广人
    Route::put('spread', 'v1.agent.AgentManage/editSpread');
    //头部统计
    Route::get('statistics', 'v1.agent.AgentManage/get_badge');
    //推广人列表
    Route::get('stair', 'v1.agent.AgentManage/get_stair_list');
    //推广人头部统计
    Route::get('stair/statistics', 'v1.agent.AgentManage/get_stair_badge');
    //统计推广订单列表
    Route::get('stair/order', 'v1.agent.AgentManage/get_stair_order_list');
    //统计推广订单列表头部
    Route::get('stair/order/statistics', 'v1.agent.AgentManage/get_stair_order_badge');
    //清除上级推广人
    Route::put('stair/delete_spread/:uid', 'v1.agent.AgentManage/delete_spread');
    //查看公众号推广二维码
    Route::get('look_code', 'v1.agent.AgentManage/look_code');
    //查看小程序推广二维码
    Route::get('look_xcx_code', 'v1.agent.AgentManage/look_xcx_code');
    //查看H5推广二维码
    Route::get('look_h5_code', 'v1.agent.AgentManage/look_h5_code');
    //积分配置编辑表单
    Route::get('config/edit_basics', 'v1.setting.SystemConfig/edit_basics');
    //积分配置保存数据
    Route::post('config/save_basics', 'v1.setting.SystemConfig/save_basics');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
