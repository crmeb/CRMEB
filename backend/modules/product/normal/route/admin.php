<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

/**
 * 实物商品后台管理路由
 * 路由前缀：/adminapi/normal/
 */

use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use app\adminapi\middleware\AdminAuthTokenMiddleware;
use modules\product\normal\controller\admin\ProductController;
use modules\product\normal\controller\admin\OrderController;
use modules\product\normal\controller\admin\StatisticController;

Route::group('normal', function () {
    /** 商品管理 */
    Route::group(function () {
        Route::get('product/list', [ProductController::class, 'list'])->option(['real_name' => '实物商品列表']);
        Route::get('product/type_header', [ProductController::class, 'typeHeader'])->option(['real_name' => '实物商品各类型数量']);
        Route::post('product', [ProductController::class, 'save'])->option(['real_name' => '创建实物商品']);
        Route::put('product/:id', [ProductController::class, 'update'])->option(['real_name' => '更新实物商品']);
        Route::put('product/status/:id/:status', [ProductController::class, 'setStatus'])->option(['real_name' => '实物商品上下架']);
        Route::delete('product/:id', [ProductController::class, 'delete'])->option(['real_name' => '删除实物商品']);
        Route::get('product/:id', [ProductController::class, 'read'])->option(['real_name' => '实物商品详情']);
        Route::post('product/batch_delete', [ProductController::class, 'batchDelete'])->option(['real_name' => '批量删除实物商品']);
        Route::post('product/batch_recover', [ProductController::class, 'batchRecover'])->option(['real_name' => '批量恢复实物商品']);
        Route::put('product/batch_status', [ProductController::class, 'batchSetStatus'])->option(['real_name' => '批量上下架实物商品']);
        Route::post('product/batch_setting', [ProductController::class, 'batchSetting'])->option(['real_name' => '批量设置实物商品']);
        Route::delete('product/full_del/:id', [ProductController::class, 'fullDel'])->option(['real_name' => '彻底删除实物商品']);
    })->option(['parent' => 'normal', 'cate_name' => '实物商品管理']);

    /** 实物商品统计 */
    Route::group(function () {
        Route::get('statistic/get_basic', [StatisticController::class, 'getBasic'])->option(['real_name' => '实物商品基础统计']);
        Route::get('statistic/get_trend', [StatisticController::class, 'getTrend'])->option(['real_name' => '实物商品趋势统计']);
        Route::get('statistic/get_product_ranking', [StatisticController::class, 'getProductRanking'])->option(['real_name' => '实物商品排行统计']);
        Route::get('statistic/get_excel', [StatisticController::class, 'getExcel'])->option(['real_name' => '实物商品统计导出']);
    })->option(['parent' => 'normal', 'cate_name' => '实物商品统计']);

    /** 订单管理 */
    Route::group(function () {
        Route::get('order/statistics', [OrderController::class, 'statistics'])->option(['real_name' => '实物商品订单统计']);
        Route::get('order/list', [OrderController::class, 'list'])->option(['real_name' => '实物商品订单列表']);
        // 发货相关
        Route::post('order/shipment', [OrderController::class, 'shipment'])->option(['real_name' => '实物商品订单发货']);
        Route::post('order/batch_shipment', [OrderController::class, 'batchShipment'])->option(['real_name' => '实物商品批量发货']);
        Route::get('order/shipment/detail/:order_no', [OrderController::class, 'getShipmentDetail'])->option(['real_name' => '实物商品发货详情']);
        Route::get('order/shipment/delivery_persons', [OrderController::class, 'getDeliveryPersons'])->option(['real_name' => '配送员列表']);
        Route::get('order/shipment/express_temp', [OrderController::class, 'getExpressTemp'])->option(['real_name' => '电子面单模板']);
        Route::get('order/shipment/sheet_info', [OrderController::class, 'getSheetInfo'])->option(['real_name' => '发件人配置信息']);
        Route::get('order/shipment/split_cart_info/:order_no', [OrderController::class, 'getSplitCartInfo'])->option(['real_name' => '可拆单商品列表']);
        Route::post('order/split_delivery', [OrderController::class, 'splitDelivery'])->option(['real_name' => '拆单发货']);
        // 修改配送信息
        Route::put('order/update_distribution', [OrderController::class, 'updateDistribution'])->option(['real_name' => '修改配送信息']);
        // 备注|删除|改价
        Route::put('order/remark', [OrderController::class, 'remark'])->option(['real_name' => '实物商品订单备注']);
        Route::delete('order/delete/:id', [OrderController::class, 'delete'])->option(['real_name' => '实物商品订单删除']);
        Route::get('order/edit_price/:order_no', [OrderController::class, 'editPrice'])->option(['real_name' => '实物商品改价表单']);
        Route::put('order/update_price', [OrderController::class, 'updatePrice'])->option(['real_name' => '实物商品改价保存']);
        Route::put('order/update_integral', [OrderController::class, 'updateIntegral'])->option(['real_name' => '实物商品积分更新']);
        Route::post('order/confirm_receipt', [OrderController::class, 'confirmReceipt'])->option(['real_name' => '实物商品确认收货']);
        Route::put('order/edit_address', [OrderController::class, 'editAddress'])->option(['real_name' => '实物商品修改地址']);
        // 确认付款（线下支付）
        Route::post('order/confirm_pay', [OrderController::class, 'confirmPay'])->option(['real_name' => '实物商品确认付款']);
        // 批量删除
        Route::post('order/batch_delete', [OrderController::class, 'batchDelete'])->option(['real_name' => '实物商品批量删除']);
        // 订单导出
        Route::get('order/export', [OrderController::class, 'export'])->option(['real_name' => '实物商品订单导出']);
        // 操作记录
        Route::get('order/record/list', [OrderController::class, 'getRecordList'])->option(['real_name' => '实物商品操作记录列表']);
        Route::get('order/get_refund_form/:order_no', [OrderController::class, 'getRefundForm'])->option(['real_name' => '实物商品退款表单']);
        Route::post('order/process_refund', [OrderController::class, 'processRefund'])->option(['real_name' => '实物商品退款执行']);
        Route::get('order/express_list', [OrderController::class, 'getExpressList'])->option(['real_name' => '快递公司列表']);
        Route::get('order/print_receipt/:order_no', [OrderController::class, 'printReceipt'])->option(['real_name' => '小票打印']);
        Route::get('order/print_picklist/:order_no', [OrderController::class, 'printPickList'])->option(['real_name' => '配货单打印']);
        // 物流查询
        Route::get('order/express/:order_no', [OrderController::class, 'getExpress'])->option(['real_name' => '物流查询']);
        // 泛匹配路由放最后
        Route::get('order/:order_no', [OrderController::class, 'read'])->option(['real_name' => '实物商品订单详情']);
    })->option(['parent' => 'normal', 'cate_name' => '实物商品订单管理']);

})->middleware([
    AllowOriginMiddleware::class,
    AdminAuthTokenMiddleware::class,
])->option(['mark' => 'normal', 'mark_name' => '实物商品管理']);
