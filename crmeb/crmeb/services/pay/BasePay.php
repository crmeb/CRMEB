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

use EasyWeChat\Payment\Order;
use crmeb\basic\BaseStorage;

/**
 * Class BasePay
 * @package crmeb\services\pay
 */
abstract class BasePay extends BaseStorage
{
    /**
     * @var string
     */
    protected $payType;

    /**
     * 设置支付类型
     * @param string $type
     * @return $this
     */
    public function setPayType(string $type)
    {
        $this->payType = $type;
        return $this;
    }

    /**
     * 设置支付类型
     * @param string $type
     * @return $this
     */
    public function authSetPayType()
    {
        if (!$this->payType) {
            if (request()->isPc()) {
                $this->payType = Order::NATIVE;
            }
            if (request()->isApp()) {
                $this->payType = Order::APP;
            }
            if (request()->isRoutine() || request()->isWechat()) {
                $this->payType = Order::JSAPI;
            }
            if (request()->isH5()) {
                $this->payType = 'h5';
            }
        }
    }


}
