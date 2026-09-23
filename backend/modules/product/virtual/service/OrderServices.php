<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\service;

use modules\product\virtual\dao\VirtualOrderDao;
use app\dao\order\OrderCenterDao;
use crmeb\basic\BaseService;

/**
 * 网盘商品订单服务
 * 订单管理业务逻辑
 */
class OrderServices extends BaseService
{
    /**
     * 订单扩展表 DAO
     */
    protected $dao;
    /** 中台订单 DAO */
    protected OrderCenterDao $centerDao;

    public function __construct(VirtualOrderDao $dao, OrderCenterDao $centerDao)
    {
        parent::__construct(app());
        $this->dao = $dao;
        $this->centerDao = $centerDao;
    }

    /**
     * 获取订单扩展信息
     * @param string $orderNo
     * @return array
     */
    public function getExtendInfo(string $orderNo): array
    {
        $info = $this->dao->getOne(['order_no' => $orderNo]);
        return $info ? $info->toArray() : [];
    }

    /**
     * 查询当前用户可访问的网盘订单。
     * @param string $orderNo 订单号
     * @param int $uid 用户 ID
     * @return array
     */
    public function getUserOrder(string $orderNo, int $uid): array
    {
        $order = $this->centerDao->get(['order_no' => $orderNo, 'uid' => $uid, 'product_type' => 'virtual', 'is_del' => 0], ['id']);
        return $order ? (is_object($order) ? $order->toArray() : $order) : [];
    }

    /**
     * 创建订单扩展记录
     * @param string $orderNo
     * @param int $productId
     * @return void
     */
    public function createOrder(string $orderNo, int $productId): void
    {
        $this->dao->save([
            'order_no'   => $orderNo,
            'product_id' => $productId,
            'status'     => 'pending_payment',
        ]);
    }

    /**
     * 更新订单状态
     * @param string $orderNo
     * @param string $status
     * @return void
     */
    public function updateStatus(string $orderNo, string $status): void
    {
        $this->dao->update(['order_no' => $orderNo], ['status' => $status]);
    }
}
