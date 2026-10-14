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
 * 订单路由
 * 旧版 StoreOrder 操作方法已迁移至各产品类型扩展端（modules/product/normal/controller/admin/OrderController）
 * 中台仅保留统一查询接口
 */

Route::group('order', function () {
    // ==================== 以下为旧版 StoreOrder 路由（已迁移至扩展端，注释保留供参考） ====================
    //获取快递信息 → modules/product/normal/OrderController::getExpressList / getExpress
    // Route::get('kuaidi_coms', [\app\adminapi\controller\order\OrderCenterController::class, 'getKuaidiComs']);
    //取消商家寄件
    // Route::post('shipment_cancel_order/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'shipmentCancelOrder']);
    //打印订单
    // Route::get('print/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'orderPrint']);
    //订单核销
    // Route::post('write', [\app\adminapi\controller\order\OrderCenterController::class, 'writeOrder']);
    //订单号核销
    // Route::put('write_update/:order_id', [\app\adminapi\controller\order\OrderCenterController::class, 'writeUpdate']);
    //获取订单编辑表单 → modules/product/normal/OrderController::editPrice
    // Route::get('edit/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'edit']);
    //修改订单 → modules/product/normal/OrderController::updatePrice
    // Route::put('update/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'update']);
    //确认收货 → modules/product/normal/OrderController::confirmReceipt
    // Route::put('take/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'takeDelivery']);
    //批量发货 → modules/product/normal/OrderController::batchShipment
    // Route::get('delivery/import_express', [\app\adminapi\controller\order\OrderCenterController::class, 'importExpress']);
    //发送货 → modules/product/normal/OrderController::shipment
    // Route::put('delivery/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'updateDelivery']);
    //获取商家寄件金额 → modules/product/normal/OrderController::getSheetInfo
    // Route::post('price', [\app\adminapi\controller\order\OrderCenterController::class, 'getPrice']);
    //获取订单可拆分商品列表 → modules/product/normal/OrderController::getSplitCartInfo
    // Route::get('split_cart_info/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'splitCartInfo']);
    //拆单发送货 → modules/product/normal/OrderController::splitDelivery
    // Route::put('split_delivery/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'splitDelivery']);
    //获取订单拆分子订单列表 → modules/product/normal/OrderController::getSplitOrderList（TODO）
    // Route::get('split_order/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'splitOrder']);
    //订单退款表单 → modules/product/normal/OrderController::getRefundForm
    // Route::get('refund/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'refund']);
    //订单退款 → modules/product/normal/OrderController::processRefund
    // Route::put('refund/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'updateRefund']);
    //获取电子面单模板 → modules/product/normal/OrderController::getExpressTemp
    // Route::get('express/temp', [\app\adminapi\controller\order\OrderCenterController::class, 'expressTemp']);
    //获取物流信息 → modules/product/normal/OrderController::getExpress
    // Route::get('express/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'getExpress']);
    //获取物流公司 → modules/product/normal/OrderController::getExpressList
    // Route::get('express_list', [\app\adminapi\controller\order\OrderCenterController::class, 'express']);
    //获取配送信息表单 → modules/product/normal/OrderController::getDistributionForm（TODO）
    // Route::get('distribution/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'distribution']);
    //修改配送信息 → modules/product/normal/OrderController::updateDistribution
    // Route::put('distribution/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'updateDistribution']);
    //获取不退款表单
    // Route::get('no_refund/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'noRefund']);
    //修改不退款理由
    // Route::put('no_refund/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'updateUnRefund']);
    //线下支付 → modules/product/normal/OrderController::confirmPay
    // Route::post('pay_offline/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'payOffline']);
    //获取退积分表单 → modules/product/normal/OrderController::updateIntegral
    // Route::get('refund_integral/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'refundIntegral']);
    //修改退积分
    // Route::put('refund_integral/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'updateRefundIntegral']);
    //获取订单状态
    // Route::get('status/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'status']);
    //面单默认配置信息 → modules/product/normal/OrderController::getSheetInfo
    // Route::get('sheet_info', [\app\adminapi\controller\order\OrderCenterController::class, 'getDeliveryInfo']);
    //电子面单模板列表 → modules/product/normal/OrderController::getExpressTemp
    // Route::get('expr/temp', [\app\adminapi\controller\order\OrderCenterController::class, 'exprTemp']);
    //打印发货单 → modules/product/normal/OrderController::printPickList
    // Route::get('print/shipping/:order_id', [\app\adminapi\controller\order\OrderCenterController::class, 'printShipping']);
    //更多操作打印电子面单 → modules/product/normal/OrderController::printReceipt
    // Route::get('order_dump/:order_id', [\app\adminapi\controller\order\OrderCenterController::class, 'orderDump']);
    //未发货的订单修改发货地址 → modules/product/normal/OrderController::editAddress
    // Route::post('edit_address/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'editAddress']);

    // ==================== 发票路由（不变） ====================
    //发票列表头部统计
    Route::get('invoice/chart', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'chart'])->name('StoreOrderorInvoiceChart')->option(['real_name' => '发票列表头部统计']);
    //申请发票列表
    Route::get('invoice/list', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'list'])->name('StoreOrderorInvoiceList')->option(['real_name' => '申请发票列表']);
    //设置发票状态
    Route::post('invoice/set/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'setInvoice'])->name('StoreOrderorInvoiceSet')->option(['real_name' => '设置发票状态']);
    //开票订单详情
    Route::get('invoice_order_info/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'orderInfo'])->name('StoreOrderorInvoiceOrderInfo')->option(['real_name' => '开票订单详情']);
    //获取发票开具页面iframe地址
    Route::get('invoice_issuance_url/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'invoiceIssuanceUrl'])->name('invoiceIssuanceUrl')->option(['real_name' => '获取发票开具页面iframe地址']);
    //保存发票信息
    Route::post('save_invoice_info/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'saveInvoiceInfo'])->name('saveInvoiceInfo')->option(['real_name' => '保存发票信息']);
    //电子发票分类
    Route::get('invoice_category', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'invoiceCategory'])->name('invoiceCategory')->option(['real_name' => '电子发票分类']);
    //开具发票
    Route::post('invoice_issuance', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'invoiceIssuance'])->name('invoiceIssuance')->option(['real_name' => '开具发票']);
    //查看发票详情
    Route::get('invoice_info/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'invoiceInfo'])->name('invoiceInfo')->option(['real_name' => '查看发票详情']);
    //开具负数发票
    Route::get('red_invoice_issuance/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'redInvoiceIssuance'])->name('redInvoiceIssuance')->option(['real_name' => '开具负数发票']);
    //下载发票
    Route::get('down_invoice/:id', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'downInvoice'])->name('downInvoice')->option(['real_name' => '下载发票']);
    //电子发票配置
    Route::get('elec_invoice_config', [\app\adminapi\controller\order\StoreOrderInvoiceController::class, 'elecInvoiceConfig'])->name('elecInvoiceConfig')->option(['real_name' => '电子发票配置']);

    // ==================== 订单中台核心接口 ====================
    //订单中台列表
    Route::get('center/list', [\app\adminapi\controller\order\OrderCenterController::class, 'index'])->option(['real_name' => '订单中台列表']);
    //订单中台统计
    Route::get('center/stat', [\app\adminapi\controller\order\OrderCenterController::class, 'stat'])->option(['real_name' => '订单中台统计']);
    //订单中台详情
    Route::get('center/detail/:id', [\app\adminapi\controller\order\OrderCenterController::class, 'detail'])->option(['real_name' => '订单中台详情']);
    //订单中台备注
    Route::put('center/remark', [\app\adminapi\controller\order\OrderCenterController::class, 'remark'])->option(['real_name' => '订单中台备注']);
    //订单中台删除
    Route::delete('center/delete', [\app\adminapi\controller\order\OrderCenterController::class, 'delete'])->option(['real_name' => '订单中台删除']);
    //订单中台分类列表
    Route::get('center/categories', [\app\adminapi\controller\order\OrderCenterController::class, 'categories'])->option(['real_name' => '订单中台分类列表']);
    //订单中台筛选选项
    Route::get('center/filter_options', [\app\adminapi\controller\order\OrderCenterController::class, 'filterOptions'])->option(['real_name' => '订单中台筛选选项']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'order', 'mark_name' => '订单管理']);

/**
 * 售后单管理（数据驱动，与 refund 退款系统分离）
 * 售后类型：refund_only/return_refund/exchange/resend/repair
 * 规则由 AfterSaleServices 按产品类型 Handler 分流
 */
Route::group('after_sale', function () {
    //售后单列表（支持售后单号/订单号/状态/类型/产品类型筛选）
    Route::get('list', [\app\adminapi\controller\order\AfterSaleController::class, 'getList'])->option(['real_name' => '售后单列表']);
    //售后单详情（含时间线+审核信息+管理备注）
    Route::get('detail/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'detail'])->option(['real_name' => '售后单详情']);
    //审核售后（通过/拒绝，拒绝需填原因）
    Route::post('audit/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'audit'])->option(['real_name' => '审核售后']);
    //商家确认收货（用户退货后，按类型分流后续流程）
    Route::post('receive/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'receive'])->option(['real_name' => '商家确认收货']);
    //执行退款（仅退款/退货退款的退款环节）
    Route::post('refund/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'refund'])->option(['real_name' => '执行退款']);
    //商家补发商品（换货/补发/维修完成后寄出，填物流单号）
    Route::post('resend/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'resend'])->option(['real_name' => '商家补发商品']);
    //开始维修（维修类型售后，商家确认收到维修件）
    Route::post('start_repair/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'startRepair'])->option(['real_name' => '开始维修']);
    //维修完成（维修完成后进入补发流程）
    Route::post('repair_complete/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'repairComplete'])->option(['real_name' => '维修完成']);
    //更新管理备注
    Route::put('remark/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'remark'])->option(['real_name' => '售后管理备注']);
    //查询售后物流进度（退货物流/补发物流，type=return|resend）
    Route::get('express/:id', [\app\adminapi\controller\order\AfterSaleController::class, 'express'])->option(['real_name' => '查询售后物流进度']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'after_sale', 'mark_name' => '售后管理']);
