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

namespace crmeb\services\pay;


use crmeb\basic\BaseManager;
use think\facade\Config;
use think\helper\Str;

/**
 * 第三方支付
 * Class Pay
 * @package crmeb\services\pay
 */
class Pay extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\driver\\pay\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('pay.default', 'wechat_pay');
    }

    /**
     * 重写驱动类解析，优先从子目录加载 Driver.php
     * @param string $type
     * @return string
     */
    protected function resolveClass(string $type): string
    {
        $studly = Str::studly($type);

        // 优先：子目录 Driver.php（新结构 driver/pay/WechatPay/Driver.php）
        $class = $this->namespace . $studly . '\\Driver';
        if (class_exists($class)) {
            return $class;
        }

        // 兼容：平级文件（旧结构 driver/pay/WechatPay.php）
        $class = $this->namespace . $studly;
        if (class_exists($class)) {
            return $class;
        }

        throw new \InvalidArgumentException("Driver [{$type}] not supported.");
    }

}