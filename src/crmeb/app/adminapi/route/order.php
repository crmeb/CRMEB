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
 * 订单路由
 */
Route::group('order', function () {
    //打印订单
    Route::get('print/:id', 'v1.order.StoreOrder/order_print')->name('StoreOrderPrint');
    //订单列表
    Route::get('list', 'v1.order.StoreOrder/lst')->name('StoreOrderList');
    //订单数据
    Route::get('chart', 'v1.order.StoreOrder/chart')->name('StoreOrderChart');
    //订单核销
    Route::post('write', 'v1.order.StoreOrder/write_order')->name('writeOrder');
    //订单号核销
    Route::put('write_update/:order_id', 'v1.order.StoreOrder/write_update')->name('writeOrderUpdate');
    //获取订单编辑表格
    Route::get('edit/:id', 'v1.order.StoreOrder/edit')->name('StoreOrderEdit');
    //修改订单
    Route::put('update/:id', 'v1.order.StoreOrder/update')->name('StoreOrderUpdate');
    //确认收货
    Route::put('take/:id', 'v1.order.StoreOrder/take_delivery')->name('StoreOrderTakeDelivery');
    //发送货
    Route::put('delivery/:id', 'v1.order.StoreOrder/update_delivery')->name('StoreOrderUpdateDelivery');
    //订单退款表格
    Route::get('refund/:id', 'v1.order.StoreOrder/refund')->name('StoreOrderRefund');
    //订单退款
    Route::put('refund/:id', 'v1.order.StoreOrder/update_refund')->name('StoreOrderUpdateRefund');
    //获取物流信息
    Route::get('express/:id', 'v1.order.StoreOrder/get_express')->name('StoreOrderUpdateExpress');
    //获取物流公司
    Route::get('express_list', 'v1.order.StoreOrder/express')->name('StoreOrdeRexpressList');
    //订单详情
    Route::get('info/:id', 'v1.order.StoreOrder/order_info')->name('StoreOrderorInfo');
    //获取配送信息表格
    Route::get('distribution/:id', 'v1.order.StoreOrder/distribution')->name('StoreOrderorDistribution');
    //修改配送信息
    Route::put('distribution/:id', 'v1.order.StoreOrder/update_distribution')->name('StoreOrderorUpdateDistribution');
    //获取不退款表格
    Route::get('no_refund/:id', 'v1.order.StoreOrder/no_refund')->name('StoreOrderorNoRefund');
    //修改不退款理由
    Route::put('no_refund/:id', 'v1.order.StoreOrder/update_un_refund')->name('StoreOrderorUpdateNoRefund');
    //线下支付
    Route::post('pay_offline/:id', 'v1.order.StoreOrder/pay_offline')->name('StoreOrderorPayOffline');
    //获取退积分表格
    Route::get('refund_integral/:id', 'v1.order.StoreOrder/refund_integral')->name('StoreOrderorRefundIntegral');
    //修改退积分
    Route::put('refund_integral/:id', 'v1.order.StoreOrder/update_refund_integral')->name('StoreOrderorUpdateRefundIntegral');
    //修改备注信息
    Route::put('remark/:id', 'v1.order.StoreOrder/remark')->name('StoreOrderorRemark');
    //获取订单状态
    Route::get('status/:id', 'v1.order.StoreOrder/status')->name('StoreOrderorStatus');
    //删除订单单个
    Route::delete('del/:id', 'v1.order.StoreOrder/del')->name('StoreOrderorDel');
    //批量删除订单
    Route::post('dels', 'v1.order.StoreOrder/del_orders')->name('StoreOrderorDels');
    //面单默认配置信息
    Route::get('sheet_info', 'v1.order.StoreOrder/getDeliveryInfo');

    //线下收银列表
    Route::get('scan_list', 'v1.order.OtherOrder/scan_list')->name('ScanList');
    //发票列表头部统计
    Route::get('invoice/chart', 'v1.order.StoreOrderInvoice/chart')->name('StoreOrderorInvoiceChart');
    //申请发票列表
    Route::get('invoice/list', 'v1.order.StoreOrderInvoice/list')->name('StoreOrderorInvoiceList');
    //设置发票状态
    Route::post('invoice/set/:id', 'v1.order.StoreOrderInvoice/set_invoice')->name('StoreOrderorInvoiceSet');
    //开票订单详情
    Route::get('invoice_order_info/:id', 'v1.order.StoreOrderInvoice/orderInfo')->name('StoreOrderorInvoiceOrderInfo');
    //配送员列表
    Route::get('delivery/index', 'v1.order.DeliveryService/index');
    //新增配送员选择用户列表
    Route::get('delivery/create', 'v1.order.DeliveryService/create');
    //新增配送表单
    Route::get('delivery/add', 'v1.order.DeliveryService/add');
    //保存新建的数据
    Route::post('delivery/save', 'v1.order.DeliveryService/save');
    //编辑配送员表单
    Route::get('delivery/:id/edit', 'v1.order.DeliveryService/edit');
    //保存编辑的数据
    Route::put('delivery/update/:id', 'v1.order.DeliveryService/update');
    //删除
    Route::delete('delivery/del/:id', 'v1.order.DeliveryService/delete');
    //修改状态
    Route::get('delivery/set_status/:id/:status', 'v1.order.DeliveryService/set_status');
    //订单列表获取配送员
    Route::get('delivery/list', 'v1.order.DeliveryService/get_delivery_list');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
