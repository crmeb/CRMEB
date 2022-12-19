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

/**
 * 支付接口类
 * Interface PayInterface
 * @package crmeb\services\pay
 */
interface PayInterface
{

    /**
     * 设置支付类型
     * @param string $type 支付类型
     * @return $this
     */
    public function setPayType(string $type);

    /**
     * 创建支付
     * @param string $orderId 订单号
     * @param string $totalFee 支付金额
     * @param string $attach 回调内容
     * @param string $body 支付body
     * @param string $detail 详情
     * @param string $tradeType 支付类型
     * @param array $options 其他参数
     * @return mixed
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = []);

    /**
     * 企业支付到零钱
     * @param string $openid openid
     * @param string $orderId 订单id
     * @param string $amount 支付金额
     * @param array $options 其他参数
     * @return mixed
     */
    public function merchantPay(string $openid, string $orderId, string $amount, array $options = []);

    /**
     * 退款
     * @param string $outTradeNo 退款单号
     * @param string $totalAmount 退款金额
     * @param string $refund_id 退款
     * @param array $options 其他参数
     * @return mixed
     */
    public function refund(string $outTradeNo, array $options = []);

    /**
     * 查询订单
     * @param string $outTradeNo 退款单号
     * @param string $outRequestNo 支付商户单号
     * @param array $other 其他参数
     * @return mixed
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = []);

    /**
     * 支付回调
     * @return mixed
     */
    public function handleNotify();

}
