<?php
// +----------------------------------------------------------------------
// | 模板消息配置
// +----------------------------------------------------------------------

return [
    //默认支付模式
    'default' => 'wechat',
    //记录发送日志
    'isLog' => true,
    //驱动模式
    'stores' => [
        //微信
        'wechat' => [
            //短信模板id
            'template_id' => [
                //订单生成通知
                'ORDER_CREATE' => 'OPENTM205213550',
                //支付成功
                'ORDER_PAY_SUCCESS' => 'OPENTM207791277',
                //订单发货提醒(快递)
                'ORDER_POSTAGE_SUCCESS' => 'OPENTM200565259',
                //订单发货提醒(送货)
                'ORDER_DELIVER_SUCCESS' => 'OPENTM207707249',
                //订单收货通知
                'ORDER_TAKE_SUCCESS' => 'OPENTM413386489',
                //退款进度通知
                'ORDER_REFUND_STATUS' => 'OPENTM410119152',
                //帐户资金变动提醒
                'USER_BALANCE_CHANGE' => 'OPENTM405847076',
                //客服通知提醒
                'SERVICE_NOTICE' => 'OPENTM204431262',
                //服务进度提醒
                'ADMIN_NOTICE' => 'OPENTM408237350',
                //拼团成功通知
                'ORDER_USER_GROUPS_SUCCESS' => 'OPENTM407456411',
                //拼团失败通知
                'ORDER_USER_GROUPS_LOSE' => 'OPENTM401113750',
                //开团成功
                'OPEN_PINK_SUCCESS' => 'OPENTM414349441',
                //砍价成功
                'BARGAIN_SUCCESS' => 'OPENTM410292733',
            ],
        ],
        //订阅消息
        'subscribe' => [
            'template_id' => [
                //订单发货提醒(送货)
                'ORDER_POSTAGE_SUCCESS' => 1128,
                //提现成功通知
                'USER_EXTRACT' => 1470,
                //确认收货通知
                'OREDER_TAKEVER' => 1481,
                //订单取消
                'ORDER_CLONE' => 1134,
                //订单发货提醒(快递)
                'ORDER_DELIVER_SUCCESS' => 1458,
                //拼团成功
                'PINK_TRUE' => 3098,
                //砍价成功
                'BARGAIN_SUCCESS' => 2727,
                //核销成功通知
                'ORDER_WRITE_OFF' => 3116,
                //新订单提醒
                'ORDER_NEW' => 1476,
                //退款通知
                'ORDER_REFUND' => 1451,
                //充值成功
                'RECHARGE_SUCCESS' => 755,
                //订单支付成功
                'ORDER_PAY_SUCCESS' => 1927,
                //申请退款通知 管理员提醒
                'ORDER_REFUND_STATUS' => 1468,
                //积分到账提醒
                'INTEGRAL_ACCOUT' => 335,
                //拼团状态通知
                'PINK_STATUS' => 3353,
            ],
        ],
    ]
];