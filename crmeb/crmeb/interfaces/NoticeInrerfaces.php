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

namespace crmeb\interfaces;

/**
 * 消息发送统一事件接口
 * Interface NoticeInrerfaces
 * @package crmeb\interfaces
 */
interface NoticeInrerfaces
{

    /**
     * 绑定推广关系
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function bindSpreadUid(array $data, array $notceinfo);

    /**
     * 支付成功给用户
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderPaySuccess(array $data, array $notceinfo);

    /**
     * 发货给用户
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderDeliverSuccess(array $data, array $notceinfo);

    /**
     * 发货快递给用户
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderPostageSuccess(array $data, array $notceinfo);

    /**
     * 确认收货给用户
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orederTakever(array $data, array $notceinfo);

    /**
     * 改价给用户
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function priceRevision(array $data, array $notceinfo);

    /**
     * 退款成功
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderRefund(array $data, array $notceinfo);

    /**
     * 退款未通过
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function sendOrderRefundNoStatus(array $data, array $notceinfo);

    /**
     * 充值余额
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function rechargeSuccess(array $data, array $notceinfo);

    /**
     * 充值退款
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function rechargeOrderRefundStatus(array $data, array $notceinfo);

    /**
     * 积分
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function integralAccout(array $data, array $notceinfo);

    /**
     * 佣金
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderBrokerage(array $data, array $notceinfo);

    /**
     * 砍价成功
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function bargainSuccess(array $data, array $notceinfo);

    /**
     * 拼团成功
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderUserGroupsSuccess(array $data, array $notceinfo);

    /**
     * 取消拼团
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function sendOrderPinkClone(array $data, array $notceinfo);

    /**
     * 拼团失败
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function sendOrderPinkFial(array $data, array $notceinfo);

    /**
     * 参团成功
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function canPinkSuccess(array $data, array $notceinfo);

    /**
     * 开团成功
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function openPinkSuccess(array $data, array $notceinfo);

    /**
     * 提现成功
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function userExtract(array $data, array $notceinfo);

    /**
     * 提现失败
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function userBalanceChange(array $data, array $notceinfo);

    /**
     * 提醒付款给用户
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function orderPayFalse(array $data, array $notceinfo);

    /**
     * 申请退款给客服发消息
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function sendOrderApplyRefund(array $data, array $notceinfo);

    /**
     * 新订单给客服
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function adminPaySuccessCode(array $data, array $notceinfo);

    /**
     * 提现申请给客服
     * @param array $data
     * @param array $notceinfo
     * @return mixed
     */
    public function kefuSendExtractApplication(array $data, array $notceinfo);

    /**
     * 确认收货给客服
     * @param array $data
     * @return mixed
     */
    public function sendAdminConfirmTakeOver(array $data, array $notceinfo);
}
