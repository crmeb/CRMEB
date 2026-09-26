<?php
/**
 * 实物商品事件监听器注册
 * 事件名使用大驼峰字符串，与服务层 event() 触发保持一致
 */
return [
    'listen' => [

        // ================================================================
        // 订单生命周期事件（触发方：app/ 层订单中台流水线）
        // ================================================================

        /** 支付成功：同步中台状态，推进扩展表为待发货/待到店 + 发送通知 */
        'OrderPaySuccess' => [
            \modules\product\normal\listener\OrderPaidListener::class,
            \modules\product\normal\listener\OrderPaySuccessNoticeListener::class,
        ],

        /** 订单取消：释放库存，更新中台状态，推进扩展表状态 */
        'OrderCancel' => [
            \modules\product\normal\listener\OrderCancelListener::class,
        ],

        /** 订单完成：推进扩展表状态为已完成 */
        'OrderComplete' => [
            \modules\product\normal\listener\OrderCompleteListener::class,
        ],

        /** 发货/核销：推进扩展表状态为待收货 + 发送通知 */
        'OrderDelivery' => [
            \modules\product\normal\listener\OrderDeliveryListener::class,
            \modules\product\normal\listener\OrderDeliveryNoticeListener::class,
        ],

        /** 确认收货：无需安装→待评价，需安装→待安装（创建履约记录）+ 发送通知 */
        'OrderTake' => [
            \modules\product\normal\listener\OrderTakeListener::class,
            \modules\product\normal\listener\OrderTakeNoticeListener::class,
        ],

        // ================================================================
        // 售后事件（触发方：app/ 层售后流程）
        // ================================================================

        /** 退款单创建后：扩展表标记退款中状态 + 发送退款申请通知 */
        'OrderRefundCreateAfter' => [
            \modules\product\normal\listener\OrderRefundCreateAfterListener::class,
            \modules\product\normal\listener\OrderRefundCreateAfterNoticeListener::class,
        ],

        /** 退款成功：更新扩展表和中台退款状态 + 发送通知 */
        'OrderRefundSuccess' => [
            \modules\product\normal\listener\OrderRefundSuccessListener::class,
            \modules\product\normal\listener\OrderRefundSuccessNoticeListener::class,
        ],

        /** 退款拒绝：恢复扩展表订单原状态 + 发送通知 */
        'OrderRefundRefuse' => [
            \modules\product\normal\listener\OrderRefundRefuseListener::class,
            \modules\product\normal\listener\OrderRefundRefuseNoticeListener::class,
        ],

        /** 退款取消：恢复扩展表订单原状态 */
        'OrderRefundCancelAfter' => [
            \modules\product\normal\listener\OrderRefundCancelAfterListener::class,
        ],

        // ================================================================
        // 订单操作事件（触发方：app/ 层业务操作）
        // ================================================================

        /** 评价完成：推进扩展表状态为已评价 */
        'OrderComment' => [
            \modules\product\normal\listener\OrderCommentListener::class,
        ],

        /** 下单成功：发送下单成功通知 */
        'OrderCreateAfter' => [
            \modules\product\normal\listener\OrderCreateAfterNoticeListener::class,
        ],

        /** 删除订单：标记扩展表逻辑删除 */
        'OrderDelete' => [
            \modules\product\normal\listener\OrderDeleteListener::class,
        ],

        /** 改价：同步更新扩展表实付金额 + 发送改价通知 */
        'OrderPriceChange' => [
            \modules\product\normal\listener\OrderPriceChangeListener::class,
            \modules\product\normal\listener\OrderPriceChangeNoticeListener::class,
        ],

        // ================================================================
        // 扩展数据事件（触发方：app/ 层列表/详情接口，扩展模块补充数据）
        // ================================================================

        /** 商品批量设置物流信息 */
        'ProductBatchLogistics' => [
            \modules\product\normal\listener\ProductBatchLogisticsListener::class,
        ],

        /** 订单中心列表扩展：补充 delivery_type 等扩展字段（兼容旧数据） */
        'OrderCenterListExtend' => [
            \modules\product\normal\listener\OrderCenterListExtendListener::class,
        ],

        /** 订单中心详情扩展：从 extra_info 提取 contact/delivery/service 到顶层 */
        'OrderCenterDetailExtend' => [
            \modules\product\normal\listener\OrderCenterDetailExtendListener::class,
        ],

        // ================================================================
        // 商品详情扩展事件（触发方：CenterProductServices::getDetail）
        // ================================================================

        /** 商品详情扩展：注入 params_list/protection_list/label_list/custom_form/type_info/fulfillment_preview */
        'ProductDetailExtend' => [
            \modules\product\normal\listener\ProductDetailExtendListener::class,
        ],

        // ================================================================
        // 数据代理事件（触发方：app 层 CenterProductServices / OutCenterProductServices）
        // ================================================================

        /** 实物商品 SKU 数据代理：代理 app 层调用 NormalProductSkuDao，避免直接引用 modules */
        'ProductTypeDataProxy' => [
            \modules\product\normal\listener\ProductTypeDataProxyListener::class,
        ],

        // ================================================================
        // MCP 工具注册事件（触发方：service_mcp 注册中心，每次 tools/list 与 tools/call 重建）
        // ================================================================

        /** 向 MCP 服务贡献实物商品读取/新增/编辑工具 */
        'McpToolsRegister' => [
            \modules\product\normal\listener\McpToolsRegisterListener::class,
        ],
    ],
];
