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

namespace crmeb\services\pay;


use crmeb\basic\BaseManager;
use crmeb\services\pay\storage\AliPay;
use crmeb\services\pay\storage\V3WechatPay;
use crmeb\services\pay\storage\WechatPay;
use think\facade\Config;

/**
 * 第三方支付
 * Class AllinPay
 * @package crmeb\services\pay
 * @mixin WechatPay
 */
class Pay extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\pay\\storage\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('pay.default', 'wechat_pay');
    }

}
