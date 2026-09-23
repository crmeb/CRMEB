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
     * 设置支付类型（从 options 中读取，不再自行判断平台）
     * pay_type 由 PayServices 从 plugin.json 的 pay_type_map 读取后传入
     * @param array $options
     */
    public function authSetPayType(array $options = [])
    {
        if (array_key_exists('pay_type', $options) || !$this->payType) {
            $this->payType = $options['pay_type'] ?? '';
        }
    }


}
