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
 * 直播相关路由
 */
Route::group('live', function () {

    //主播列表
    Route::get('anchor/list', 'v1.marketing.live.LiveAnchor/list');
    //添加修改主播表单
    Route::get('anchor/add/:id', 'v1.marketing.live.LiveAnchor/add');
    //保存主播数据
    Route::post('anchor/save', 'v1.marketing.live.LiveAnchor/save');
    //删除主播
    Route::delete('anchor/del/:id', 'v1.marketing.live.LiveAnchor/delete');
    //设置是否显示
    Route::get('anchor/set_show/:id/:is_show', 'v1.marketing.live.LiveAnchor/setShow');
    //同步主播
    Route::get('anchor/syncAnchor', 'v1.marketing.live.LiveAnchor/syncAnchor');

    //直播商品列表
    Route::get('goods/list', 'v1.marketing.live.LiveGoods/list');
    //生成直播商品
    Route::post('goods/create', 'v1.marketing.live.LiveGoods/create');
    //添加修改商品
    Route::post('goods/add', 'v1.marketing.live.LiveGoods/add');
    //商品详情
    Route::get('goods/detail/:id', 'v1.marketing.live.LiveGoods/detail');
    //商品重新审核
    Route::get('goods/audit/:id', 'v1.marketing.live.LiveGoods/audit');
    //商品撤回审核
    Route::get('goods/resestAudit/:id', 'v1.marketing.live.LiveGoods/resetAudit');
    //删除商品
    Route::delete('goods/del/:id', 'v1.marketing.live.LiveGoods/delete');
    //设置是否显示
    Route::get('goods/set_show/:id/:is_show', 'v1.marketing.live.liveGoods/setShow');
    //同步直播商品状态
    Route::get('goods/syncGoods', 'v1.marketing.live.liveGoods/syncGoods');

    //直播间列表
    Route::get('room/list', 'v1.marketing.live.LiveRoom/list');
    //直播间添加
    Route::post('room/add', 'v1.marketing.live.LiveRoom/add');
    //直播间详情
    Route::get('room/detail/:id', 'v1.marketing.live.LiveRoom/detail');
    //直播间添加商品
    Route::post('room/add_goods', 'v1.marketing.live.LiveRoom/addGoods');
    //删除直播
    Route::delete('room/del/:id', 'v1.marketing.live.LiveRoom/delete');
    //设置是否显示
    Route::get('room/set_show/:id/:is_show', 'v1.marketing.live.LiveRoom/setShow');
    //同步直播间状态
    Route::get('room/syncRoom', 'v1.marketing.live.LiveRoom/syncRoom');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
