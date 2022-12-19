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
namespace app\adminapi\validate\notification;

use think\Validate;

/**
 *
 * Class SmsConfigValidate
 * @package app\adminapi\validates
 */
class SmsConfigValidate extends Validate
{
    /**
     * 定义验证规则
     * @var array
     */
    protected $rule = [
        'sms_account' => ['require'],
        'sms_token' => ['require'],
    ];

    /**
     * 定义错误信息
     * @var array
     */
    protected $message = [
        'sms_account.require' => '400387',
        'sms_token.require' => '400388',
    ];
}
