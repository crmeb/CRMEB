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

return [
    //默认驱动模式
    'default' => 'wechat',
    //记录发送日志
    'isLog' => true,
    //驱动模式
    'stores' => [
        //微信
        'wechat' => [
            //短信模板id
            'template_id' => [
                //绑定推广关系
                'BIND_SPREAD_UID' => 'OPENTM410239001',
                //支付成功
                'ORDER_PAY_SUCCESS' => 'OPENTM207791277',
                //订单发货提醒(送货)
                'ORDER_DELIVER_SUCCESS' => 'OPENTM207707249',
                //订单发货提醒(快递)
                'ORDER_POSTAGE_SUCCESS' => 'OPENTM200565259',
                //订单收货通知
                'ORDER_TAKE_SUCCESS' => 'OPENTM413386489',
                //改价成功通知
                'PRICE_REVISION' => 'OPENTM410137124',
                //退款成功通知,拒绝退款通知
                'ORDER_REFUND_STATUS'=>'OPENTM410119152',
                //充值成功通知
                'RECHARGE_SUCCESS' => 'OPENTM411706852',
                //积分到账通知
                'INTEGRAL_ACCOUT' => 'OPENTM418252271',
                //佣金到账
                'ORDER_BROKERAGE' => 'OPENTM409909643',
                //砍价成功
                'BARGAIN_SUCCESS' => 'OPENTM410292733',
                //拼团成功通知,参团成功
                'ORDER_USER_GROUPS_SUCCESS' => 'OPENTM407456411',
                //取消拼团,拼团失败
                'ORDER_USER_GROUPS_LOSE'=>'OPENTM401113750',
                //开团成功
                'OPEN_PINK_SUCCESS' => 'OPENTM414349441',
                //提现成功通知
                'USER_EXTRACT' => 'OPENTM418265600',
                //提现失败通知
                'USER_EXTRACT_FAIL' => 'OPENTM408529050',
                //提醒付款通知
                'ORDER_PAY_FALSE' => 'OPENTM413741318',
                //服务进度提醒
                'ADMIN_NOTICE' => 'OPENTM408237350',


//                //订单生成通知
//                'ORDER_CREATE' => 'OPENTM205213550',
//                //退款进度通知
//                'ORDER_REFUND' => 'OPENTM410119152',
//                //拼团失败通知
//                'SEND_ORDER_PINK_FIAL' => 'OPENTM401113750',
//                //充值退款通知
//                'RECHARGE_ORDER_REFUND_STATUS' => '',
//                //退款申请未通过通知
//                'SEND_ORDER_REFUND_NO_STATUS' => '',
//                //取消拼团提醒
//                'SEND_ORDER_PINK_CLONE' => '',
//                //参团成功提醒
//                'CAN_PINK_SUCCESS' => '',
//                //客服通知提醒
//                'SERVICE_NOTICE' => 'OPENTM204431262',
            ],
        ],
        //订阅消息
        'subscribe' => [
            'template_id' => [
                //绑定推广关系
                'BIND_SPREAD_UID' => 3801,
                //订单支付成功
                'ORDER_PAY_SUCCESS' => 1927,
                //订单发货提醒(快递)
                'ORDER_DELIVER_SUCCESS' => 1458,
                //订单发货提醒(送货)
                'ORDER_POSTAGE_SUCCESS' => 1128,
                //确认收货通知
                'ORDER_TAKE' => 1481,
                //退款通知
                'ORDER_REFUND' => 1451,
                //充值成功
                'RECHARGE_SUCCESS' => 755,
                //积分到账提醒
                'INTEGRAL_ACCOUT' => 335,
                //佣金到账
                'ORDER_BROKERAGE' => 14403,
                //砍价成功
                'BARGAIN_SUCCESS' => 2727,
                //拼团成功
                'PINK_TRUE' => 3098,
                //拼团状态通知
                'PINK_STATUS' => 3353,
                //提现成功通知
                'USER_EXTRACT' => 1470,






//                //订单取消
//                'ORDER_CLONE' => 1134,
//                //核销成功通知
//                'ORDER_WRITE_OFF' => 3116,
//                //新订单提醒
//                'ORDER_NEW' => 1476,
//                //申请退款通知 管理员提醒
//                'ORDER_REFUND_STATUS' => 1468,
            ],
        ],
    ]
];
