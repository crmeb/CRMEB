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
 * 商户管理 相关路由
 */
Route::group('freight', function () {
    //物流公司资源路由
    Route::resource('express', \app\adminapi\controller\freight\ExpressController::class)->except(['read'])->name('ExpressResource')->option([
        'real_name' => [
            'index' => '获取物流公司列表',
            'create' => '获取物流公司表单',
            'save' => '保存物流公司',
            'edit' => '获取修改物流公司表单',
            'update' => '修改物流公司',
            'delete' => '删除物流公司'
        ],
    ]);
    //修改状态
    Route::put('express/set_status/:id/:status', [\app\adminapi\controller\freight\ExpressController::class, 'setStatus'])->option(['real_name' => '修改物流公司状态']);
    //同步物流快递公司
    Route::get('express/sync_express', [\app\adminapi\controller\freight\ExpressController::class, 'syncExpress'])->option(['real_name' => '同步物流公司']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'freight', 'mark_name' => '物流管理']);

/** 运费模板管理 */
Route::group('freight', function () {
    Route::get('shipping_templates/list', [\app\adminapi\controller\freight\ShippingTemplatesController::class, 'tempList'])->option(['real_name' => '运费模板列表']);
    Route::get('shipping_templates/:id/edit', [\app\adminapi\controller\freight\ShippingTemplatesController::class, 'edit'])->option(['real_name' => '运费模板编辑']);
    Route::post('shipping_templates/save/:id', [\app\adminapi\controller\freight\ShippingTemplatesController::class, 'save'])->option(['real_name' => '运费模板保存']);
    Route::delete('shipping_templates/del/:id', [\app\adminapi\controller\freight\ShippingTemplatesController::class, 'delete'])->option(['real_name' => '运费模板删除']);
    Route::get('shipping_templates/city_list', [\app\adminapi\controller\freight\ShippingTemplatesController::class, 'cityList'])->option(['real_name' => '运费模板城市数据']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
])->option(['mark' => 'freight', 'mark_name' => '运费模板管理']);
