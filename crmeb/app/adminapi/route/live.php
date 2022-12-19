<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

use think\facade\Route;

/**
 * 直播相关路由
 */
Route::group('live', function () {

    //主播列表
    Route::get('anchor/list', 'v1.marketing.live.LiveAnchor/list')->option(['real_name' => '主播列表']);
    //添加修改主播表单
    Route::get('anchor/add/:id', 'v1.marketing.live.LiveAnchor/add')->option(['real_name' => '添加修改主播表单']);
    //保存主播数据
    Route::post('anchor/save', 'v1.marketing.live.LiveAnchor/save')->option(['real_name' => '保存主播数据']);
    //删除主播
    Route::delete('anchor/del/:id', 'v1.marketing.live.LiveAnchor/delete')->option(['real_name' => '删除主播']);
    //设置是否显示
    Route::get('anchor/set_show/:id/:is_show', 'v1.marketing.live.LiveAnchor/setShow')->option(['real_name' => '设置主播是否显示']);
    //同步主播
    Route::get('anchor/syncAnchor', 'v1.marketing.live.LiveAnchor/syncAnchor')->option(['real_name' => '同步主播']);

    //直播商品列表
    Route::get('goods/list', 'v1.marketing.live.LiveGoods/list')->option(['real_name' => '直播商品列表']);
    //生成直播商品
    Route::post('goods/create', 'v1.marketing.live.LiveGoods/create')->option(['real_name' => '生成直播商品']);
    //添加修改商品
    Route::post('goods/add', 'v1.marketing.live.LiveGoods/add')->option(['real_name' => '添加修改直播商品']);
    //商品详情
    Route::get('goods/detail/:id', 'v1.marketing.live.LiveGoods/detail')->option(['real_name' => '直播商品详情']);
    //商品重新审核
    Route::get('goods/audit/:id', 'v1.marketing.live.LiveGoods/audit')->option(['real_name' => '直播商品重新审核']);
    //商品撤回审核
    Route::get('goods/resestAudit/:id', 'v1.marketing.live.LiveGoods/resetAudit')->option(['real_name' => '直播商品撤回审核']);
    //删除商品
    Route::delete('goods/del/:id', 'v1.marketing.live.LiveGoods/delete')->option(['real_name' => '删除直播商品']);
    //设置是否显示
    Route::get('goods/set_show/:id/:is_show', 'v1.marketing.live.liveGoods/setShow')->option(['real_name' => '设置直播商品是否显示']);
    //同步直播商品状态
    Route::get('goods/syncGoods', 'v1.marketing.live.liveGoods/syncGoods')->option(['real_name' => '同步直播商品状态']);

    //直播间列表
    Route::get('room/list', 'v1.marketing.live.LiveRoom/list')->option(['real_name' => '直播间列表']);
    //直播间添加
    Route::post('room/add', 'v1.marketing.live.LiveRoom/add')->option(['real_name' => '直播间添加']);
    //直播间详情
    Route::get('room/detail/:id', 'v1.marketing.live.LiveRoom/detail')->option(['real_name' => '直播间详情']);
    //直播间添加商品
    Route::post('room/add_goods', 'v1.marketing.live.LiveRoom/addGoods')->option(['real_name' => '直播间添加商品']);
    //删除直播
    Route::delete('room/del/:id', 'v1.marketing.live.LiveRoom/delete')->option(['real_name' => '删除直播间']);
    //设置是否显示
    Route::get('room/set_show/:id/:is_show', 'v1.marketing.live.LiveRoom/setShow')->option(['real_name' => '设置直播间是否显示']);
    //同步直播间状态
    Route::get('room/syncRoom', 'v1.marketing.live.LiveRoom/syncRoom')->option(['real_name' => '同步直播间状态']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
