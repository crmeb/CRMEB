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
 * 订单路由
 */
Route::group('order', function () {
    //打印订单
    Route::get('print/:id', 'v1.order.StoreOrder/order_print')->name('StoreOrderPrint')->option(['real_name' => '打印订单']);
    //订单列表
    Route::get('list', 'v1.order.StoreOrder/lst')->name('StoreOrderList')->option(['real_name' => '订单列表']);
    //订单数据
    Route::get('chart', 'v1.order.StoreOrder/chart')->name('StoreOrderChart')->option(['real_name' => '订单头部数据']);
    //订单核销
    Route::post('write', 'v1.order.StoreOrder/write_order')->name('writeOrder')->option(['real_name' => '订单核销']);
    //订单号核销
    Route::put('write_update/:order_id', 'v1.order.StoreOrder/write_update')->name('writeOrderUpdate')->option(['real_name' => '订单号核销']);
    //获取订单编辑表格
    Route::get('edit/:id', 'v1.order.StoreOrder/edit')->name('StoreOrderEdit')->option(['real_name' => '获取订单编辑表单']);
    //修改订单
    Route::put('update/:id', 'v1.order.StoreOrder/update')->name('StoreOrderUpdate')->option(['real_name' => '修改订单']);
    //确认收货
    Route::put('take/:id', 'v1.order.StoreOrder/take_delivery')->name('StoreOrderTakeDelivery')->option(['real_name' => '确认收货']);
    //发送货
    Route::put('delivery/:id', 'v1.order.StoreOrder/update_delivery')->name('StoreOrderUpdateDelivery')->option(['real_name' => '订单发送货']);
    //获取订单可拆分商品列表
    Route::get('split_cart_info/:id', 'v1.order.StoreOrder/split_cart_info')->name('StoreOrderSplitCartInfo')->option(['real_name' => '获取订单可拆分商品列表']);
    //拆单发送货
    Route::put('split_delivery/:id', 'v1.order.StoreOrder/split_delivery')->name('StoreOrderSplitDelivery')->option(['real_name' => '拆单发送货']);
    //获取订单拆分子订单列表
    Route::get('split_order/:id', 'v1.order.StoreOrder/split_order')->name('StoreOrderSplitOrder')->option(['real_name' => '获取订单拆分子订单列表']);
    //订单退款表格
    Route::get('refund/:id', 'v1.order.StoreOrder/refund')->name('StoreOrderRefund')->option(['real_name' => '订单退款表单']);
    //订单退款
    Route::put('refund/:id', 'v1.order.StoreOrder/update_refund')->name('StoreOrderUpdateRefund')->option(['real_name' => '订单退款']);
    //获取物流信息
    Route::get('express/:id', 'v1.order.StoreOrder/get_express')->name('StoreOrderUpdateExpress')->option(['real_name' => '获取物流信息']);
    //获取物流公司
    Route::get('express_list', 'v1.order.StoreOrder/express')->name('StoreOrdeRexpressList')->option(['real_name' => '获取物流公司']);
    //订单详情
    Route::get('info/:id', 'v1.order.StoreOrder/order_info')->name('StoreOrderorInfo')->option(['real_name' => '订单详情']);
    //获取配送信息表格
    Route::get('distribution/:id', 'v1.order.StoreOrder/distribution')->name('StoreOrderorDistribution')->option(['real_name' => '获取配送信息表单']);
    //修改配送信息
    Route::put('distribution/:id', 'v1.order.StoreOrder/update_distribution')->name('StoreOrderorUpdateDistribution')->option(['real_name' => '修改配送信息']);
    //获取不退款表格
    Route::get('no_refund/:id', 'v1.order.StoreOrder/no_refund')->name('StoreOrderorNoRefund')->option(['real_name' => '获取不退款表单']);
    //修改不退款理由
    Route::put('no_refund/:id', 'v1.order.StoreOrder/update_un_refund')->name('StoreOrderorUpdateNoRefund')->option(['real_name' => '修改不退款理由']);
    //线下支付
    Route::post('pay_offline/:id', 'v1.order.StoreOrder/pay_offline')->name('StoreOrderorPayOffline')->option(['real_name' => '线下支付']);
    //获取退积分表格
    Route::get('refund_integral/:id', 'v1.order.StoreOrder/refund_integral')->name('StoreOrderorRefundIntegral')->option(['real_name' => '获取退积分表单']);
    //修改退积分
    Route::put('refund_integral/:id', 'v1.order.StoreOrder/update_refund_integral')->name('StoreOrderorUpdateRefundIntegral')->option(['real_name' => '修改退积分']);
    //修改备注信息
    Route::put('remark/:id', 'v1.order.StoreOrder/remark')->name('StoreOrderorRemark')->option(['real_name' => '修改备注信息']);
    //获取订单状态
    Route::get('status/:id', 'v1.order.StoreOrder/status')->name('StoreOrderorStatus')->option(['real_name' => '获取订单状态']);
    //删除订单单个
    Route::delete('del/:id', 'v1.order.StoreOrder/del')->name('StoreOrderorDel')->option(['real_name' => '删除订单单个']);
    //批量删除订单
    Route::post('dels', 'v1.order.StoreOrder/del_orders')->name('StoreOrderorDels')->option(['real_name' => '批量删除订单']);
    //面单默认配置信息
    Route::get('sheet_info', 'v1.order.StoreOrder/getDeliveryInfo')->option(['real_name' => '面单默认配置信息']);

    //获取线下付款二维码
    Route::get('offline_scan', 'v1.order.OtherOrder/offline_scan')->name('OfflineScan')->option(['real_name' => '获取线下付款二维码']);
    //线下收银列表
    Route::get('scan_list', 'v1.order.OtherOrder/scan_list')->name('ScanList')->option(['real_name' => '线下收银列表']);
    //发票列表头部统计
    Route::get('invoice/chart', 'v1.order.StoreOrderInvoice/chart')->name('StoreOrderorInvoiceChart')->option(['real_name' => '发票列表头部统计']);
    //申请发票列表
    Route::get('invoice/list', 'v1.order.StoreOrderInvoice/list')->name('StoreOrderorInvoiceList')->option(['real_name' => '申请发票列表']);
    //设置发票状态
    Route::post('invoice/set/:id', 'v1.order.StoreOrderInvoice/set_invoice')->name('StoreOrderorInvoiceSet')->option(['real_name' => '设置发票状态']);
    //开票订单详情
    Route::get('invoice_order_info/:id', 'v1.order.StoreOrderInvoice/orderInfo')->name('StoreOrderorInvoiceOrderInfo')->option(['real_name' => '开票订单详情']);
    //配送员列表
    Route::get('delivery/index', 'v1.order.DeliveryService/index')->option(['real_name' => '配送员列表']);
    //新增配送表单
    Route::get('delivery/add', 'v1.order.DeliveryService/add')->option(['real_name' => '新增配送表单']);
    //保存新建的数据
    Route::post('delivery/save', 'v1.order.DeliveryService/save')->option(['real_name' => '保存新建的配送员']);
    //编辑配送员表单
    Route::get('delivery/:id/edit', 'v1.order.DeliveryService/edit')->option(['real_name' => '编辑配送员表单']);
    //保存编辑的数据
    Route::put('delivery/update/:id', 'v1.order.DeliveryService/update')->option(['real_name' => '修改配送员']);
    //删除
    Route::delete('delivery/del/:id', 'v1.order.DeliveryService/delete')->option(['real_name' => '删除配送员']);
    //修改状态
    Route::get('delivery/set_status/:id/:status', 'v1.order.DeliveryService/set_status')->option(['real_name' => '修改配送员状态']);
    //订单列表获取配送员
    Route::get('delivery/list', 'v1.order.DeliveryService/get_delivery_list')->option(['real_name' => '订单列表获取配送员']);
    //电子面单模板列表
    Route::get('expr/temp', 'v1.order.StoreOrder/expr_temp')->option(['real_name' => '电子面单模板列表']);
    Route::get('express/temp', 'v1.order.StoreOrder/express_temp')->option(['real_name' => '快递公司电子面单模版']);
    //更多操作打印电子面单
    Route::get('order_dump/:order_id', 'v1.order.StoreOrder/order_dump')->option(['real_name' => '更多操作打印电子面单']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);

/**
 * 售后 相关路由
 */
Route::group('refund', function () {
    //售后列表
    Route::get('list', 'v1.order.RefundOrder/getRefundList')->option(['real_name' => '售后订单列表']);
    //商家同意退款，等待用户退货
    Route::get('agree/:id', 'v1.order.RefundOrder/agreeExpress')->option(['real_name' => '商家同意退款，等待用户退货']);
    //售后订单备注
    Route::put('remark/:id', 'v1.order.RefundOrder/remark')->option(['real_name' => '售后订单备注']);
    //售后订单退款表单
    Route::get('refund/:id', 'v1.order.RefundOrder/refund')->option(['real_name' => '售后订单退款表单']);
    //售后订单退款
    Route::put('refund/:id', 'v1.order.RefundOrder/refundPrice')->option(['real_name' => '售后订单退款']);
    //获取不退款表格
    Route::get('no_refund/:id', 'v1.order.RefundOrder/noRefund')->option(['real_name' => '获取不退款表单']);
    //修改不退款理由
    Route::put('no_refund/:id', 'v1.order.RefundOrder/refuseRefund')->option(['real_name' => '修改不退款理由']);
    //退款单信息
    Route::get('info/:uni', 'v1.order.RefundOrder/getRefundInfo')->option(['real_name' => '获取退款单详情']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
