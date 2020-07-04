<?php

namespace crmeb\basic;


abstract class BasePay extends BaseStorage
{

    /**
     * 支付参数配置
     * @var array
     */
    protected $configPay = [];

    /**
     * 发起支付
     * @param array|null $configPay
     * @return mixed
     */
    abstract public function pay(?array $configPay = []);

    /**
     * 退款
     * @param array|null $configPay
     * @return mixed
     */
    abstract public function refund(?array $configPay = []);

    /**
     * 设置支付参数
     * @param array $configPay
     * @return $this
     */
    public function setConfigPay(array $configPay)
    {
        $this->configPay = $configPay;
        return $this;
    }
}