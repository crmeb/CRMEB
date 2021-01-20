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
                //服务进度提醒
                'ADMIN_NOTICE' => 'OPENTM408237350',
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
                //核销成功通知
                'ORDER_WRITE_OFF' => 3116,
                //新订单提醒
                'ORDER_NEW' => 1476,
                //退款通知
                'ORDER_REFUND' => 1451,
                //订单支付成功
                'ORDER_PAY_SUCCESS' => 1927,
                //申请退款通知 管理员提醒
                'ORDER_REFUND_STATUS' => 1468,
                //积分到账提醒
                'INTEGRAL_ACCOUT' => 335,
            ],
        ],
    ]
];
