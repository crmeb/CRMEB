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

return [
    //默认扩展
    'default' => 'yihaotong',
    //单个手机每日发送上限
    'maxPhoneCount' => 10,
    //验证码每分钟发送上线
    'maxMinuteCount' => 20,
    //单个IP每日发送上限
    'maxIpCount' => 50,
    //驱动模式
    'stores' => [
        //一号通
        'yihaotong' => [
            'sms_account' => '',
            'sms_token' => ''
        ],
        //阿里云
        'aliyun' => [
            'aliyun_SignName' => '',
            'aliyun_AccessKeyId' => '',
            'aliyun_AccessKeySecret' => '',
            'aliyun_RegionId' => '',
        ],
        //腾讯云
        'tencent' => [
            'tencent_sms_app_id' => '',
            'tencent_sms_secret_id' => '',
            'tencent_sms_secret_key' => '',
            'tencent_sms_sign_name' => '',
            'tencent_sms_region' => '',
        ]
    ]
];
