<?php
// +----------------------------------------------------------------------
// | 短信配置
// +----------------------------------------------------------------------

return [
    //平台账号
    'account' => '',
    //平台秘钥
    'secret' => '',
    //驱动模式
    'stores' => [
        'sms' => [
            //单个手机每日发送上限
            'maxPhoneCount' => 10,
            //验证码每分钟发送上线
            'maxMinuteCount' => 20,
            //单个IP每日发送上限
            'maxIpCount' => 50,
            //短信模板id
            'template_id' => [
                //验证码自定义时效
                'VERIFICATION_CODE_TIME' => 538393,
                //验证码
                'VERIFICATION_CODE' => 518076,
                //支付成功
                'PAY_SUCCESS_CODE' => 520268,
                //发货提醒
                'DELIVER_GOODS_CODE' => 520269,
                //确认收货提醒
                'TAKE_DELIVERY_CODE' => 520271,
                //管理员下单提醒
                'ADMIN_PLACE_ORDER_CODE' => 520272,
                //管理员退货提醒
                'ADMIN_RETURN_GOODS_CODE' => 520274,
                //管理员支付成功提醒
                'ADMIN_PAY_SUCCESS_CODE' => 520273,
                //管理员确认收货
                'ADMIN_TAKE_DELIVERY_CODE' => 520422,
                //改价提醒
                'PRICE_REVISION_CODE' => 528288,
                //订单未支付
                'ORDER_PAY_FALSE' => 528116,
            ]
        ]
    ]
];