<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;

/**
 * 小程序后台管理路由
 */
Route::group('app', function () {

    /** 小程序 */
    Route::group(function () {
        Route::get('routine/syncSubscribe', [\modules\channel\wechat_mini\controller\admin\RoutineTemplateController::class, 'syncSubscribe'])->name('syncSubscribe')->option(['real_name' => '一键同步订阅消息']);
        Route::get('routine/info', [\modules\channel\wechat_mini\controller\admin\RoutineTemplateController::class, 'getDownloadInfo'])->option(['real_name' => '下载小程序页面数据']);
        Route::post('routine/download', [\modules\channel\wechat_mini\controller\admin\RoutineTemplateController::class, 'downloadTemp'])->option(['real_name' => '下载小程序模板']);

        Route::get('routine/ci/environment', [\modules\channel\wechat_mini\controller\admin\RoutineCIController::class, 'environment'])->option(['real_name' => '获取小程序CI运行环境']);
        Route::get('routine/ci/guide', [\modules\channel\wechat_mini\controller\admin\RoutineCIController::class, 'installGuide'])->option(['real_name' => '获取环境安装指南']);
        Route::get('routine/ci/config', [\modules\channel\wechat_mini\controller\admin\RoutineCIController::class, 'uploadConfig'])->option(['real_name' => '获取小程序上传配置']);
        Route::post('routine/ci/private_key', [\modules\channel\wechat_mini\controller\admin\RoutineCIController::class, 'savePrivateKey'])->option(['real_name' => '保存小程序上传密钥']);
        Route::post('routine/ci/upload', [\modules\channel\wechat_mini\controller\admin\RoutineCIController::class, 'upload'])->option(['real_name' => '上传小程序代码']);
        Route::post('routine/ci/preview', [\modules\channel\wechat_mini\controller\admin\RoutineCIController::class, 'preview'])->option(['real_name' => '获取小程序预览二维码']);

        Route::get('routine/scheme_list', [\modules\channel\wechat_mini\controller\admin\RoutineSchemeController::class, 'schemeList'])->name('schemeList')->option(['real_name' => '小程序外链列表']);
        Route::get('routine/scheme_form/:id', [\modules\channel\wechat_mini\controller\admin\RoutineSchemeController::class, 'schemeForm'])->name('schemeForm')->option(['real_name' => '小程序外链添加修改表单']);
        Route::post('routine/scheme_save/:id', [\modules\channel\wechat_mini\controller\admin\RoutineSchemeController::class, 'schemeSave'])->name('schemeSave')->option(['real_name' => '小程序外链添加修改保存']);
        Route::delete('routine/scheme_del/:id', [\modules\channel\wechat_mini\controller\admin\RoutineSchemeController::class, 'schemeDel'])->name('schemeDel')->option(['real_name' => '小程序外链删除']);
    })->option(['parent' => 'app', 'cate_name' => '小程序']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'channel_wechat_mini', 'mark_name' => '小程序管理']);

/**
 * 小程序直播路由（主播/直播商品/直播间管理）
 */
Route::group('live', function () {

    /** 主播 */
    Route::group(function () {
        //主播列表
        Route::get('anchor/list', [\modules\channel\wechat_mini\controller\admin\LiveAnchorController::class, 'list'])->option(['real_name' => '主播列表']);
        //添加修改主播表单
        Route::get('anchor/add/:id', [\modules\channel\wechat_mini\controller\admin\LiveAnchorController::class, 'add'])->option(['real_name' => '添加修改主播表单']);
        //保存主播数据
        Route::post('anchor/save', [\modules\channel\wechat_mini\controller\admin\LiveAnchorController::class, 'save'])->option(['real_name' => '保存主播数据']);
        //删除主播
        Route::delete('anchor/del/:id', [\modules\channel\wechat_mini\controller\admin\LiveAnchorController::class, 'delete'])->option(['real_name' => '删除主播']);
        //设置是否显示
        Route::get('anchor/set_show/:id/:is_show', [\modules\channel\wechat_mini\controller\admin\LiveAnchorController::class, 'setShow'])->option(['real_name' => '设置主播是否显示']);
    })->option(['parent' => 'live', 'cate_name' => '主播']);

    /** 直播商品 */
    Route::group(function () {
        //直播商品列表
        Route::get('goods/list', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'list'])->option(['real_name' => '直播商品列表']);
        //生成直播商品
        Route::post('goods/create', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'create'])->option(['real_name' => '生成直播商品']);
        //添加修改商品
        Route::post('goods/add', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'add'])->option(['real_name' => '添加修改直播商品']);
        //商品详情
        Route::get('goods/detail/:id', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'detail'])->option(['real_name' => '直播商品详情']);
        //商品重新审核
        Route::get('goods/audit/:id', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'audit'])->option(['real_name' => '直播商品重新审核']);
        //商品撤回审核
        Route::get('goods/resestAudit/:id', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'resetAudit'])->option(['real_name' => '直播商品撤回审核']);
        //删除商品
        Route::delete('goods/del/:id', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'delete'])->option(['real_name' => '删除直播商品']);
        //设置是否显示
        Route::get('goods/set_show/:id/:is_show', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'setShow'])->option(['real_name' => '设置直播商品是否显示']);
        //同步直播商品状态
        Route::get('goods/syncGoods', [\modules\channel\wechat_mini\controller\admin\LiveGoodsController::class, 'syncGoods'])->option(['real_name' => '同步直播商品状态']);
    })->option(['parent' => 'live', 'cate_name' => '直播商品']);

    /** 直播间 */
    Route::group(function () {
        //直播间列表
        Route::get('room/list', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'list'])->option(['real_name' => '直播间列表']);
        //直播间添加
        Route::post('room/add', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'add'])->option(['real_name' => '直播间添加']);
        //直播间详情
        Route::get('room/detail/:id', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'detail'])->option(['real_name' => '直播间详情']);
        //直播间添加商品
        Route::post('room/add_goods', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'addGoods'])->option(['real_name' => '直播间添加商品']);
        //删除直播
        Route::delete('room/del/:id', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'delete'])->option(['real_name' => '删除直播间']);
        //设置是否显示
        Route::get('room/set_show/:id/:is_show', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'setShow'])->option(['real_name' => '设置直播间是否显示']);
        //同步直播间状态
        Route::get('room/syncRoom', [\modules\channel\wechat_mini\controller\admin\LiveRoomController::class, 'syncRoom'])->option(['real_name' => '同步直播间状态']);
    })->option(['parent' => 'live', 'cate_name' => '直播间']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'live', 'mark_name' => '直播管理']);
