<?php
/**
 * 网盘产品事件监听器注册
 * 事件名使用大驼峰字符串，与服务层 event() 触发保持一致
 */
return [
    'listen' => [
        // 网盘发放由 Handler::fulfillOrder 在支付提交后直接调用，不再挂在支付成功广播上。
        // 未支付取消由 OrderCenterServices::cancelUnpaidOrder 直接推进，不再监听 OrderCancel。
        'OrderRefundCreateAfter' => [
            \modules\product\virtual\listener\OrderRefundCreateAfterListener::class,
        ],
        'OrderRefundSuccess' => [
            \modules\product\virtual\listener\OrderRefundSuccessListener::class,
        ],
        'OrderRefundRefuse' => [
            \modules\product\virtual\listener\OrderRefundRefuseListener::class,
        ],
        'OrderRefundCancelAfter' => [
            \modules\product\virtual\listener\OrderRefundCancelAfterListener::class,
        ],
        'OrderComment' => [
            \modules\product\virtual\listener\OrderCommentListener::class,
        ],
        'OrderDelete' => [
            \modules\product\virtual\listener\OrderDeleteListener::class,
        ],
        'OrderPriceChange' => [
            \modules\product\virtual\listener\OrderPriceChangeListener::class,
        ],

        /** 商品详情扩展：注入 params_list/protection_list/label_list/custom_form/type_info/fulfillment_preview */
        'ProductDetailExtend' => [
            \modules\product\virtual\listener\ProductDetailExtendListener::class,
        ],

        /** 订单中台详情扩展：已支付订单注入网盘链接与提取码（履约信息） */
        'OrderCenterDetailExtend' => [
            \modules\product\virtual\listener\OrderCenterDetailExtendListener::class,
        ],

        /** 订单中台列表扩展：合并网盘订单扩展字段（product_id/status/send_time/complete_time） */
        'OrderCenterListExtend' => [
            \modules\product\virtual\listener\OrderCenterListExtendListener::class,
        ],
    ],
];
