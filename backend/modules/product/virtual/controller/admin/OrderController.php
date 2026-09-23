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
declare(strict_types=1);

namespace modules\product\virtual\controller\admin;

use app\adminapi\controller\AuthController;
use app\services\order\OrderCenterServices;
use modules\product\virtual\service\OrderServices;

/**
 * 网盘商品后台订单控制器
 * 通过订单中台 eb_order_center 查询网盘商品订单
 */
class OrderController extends AuthController
{
    /**
     * 订单列表
     * 从中台表查询 product_type=virtual 的订单
     * @param OrderCenterServices $centerServices
     * @return \think\Response
     */
    public function list(OrderCenterServices $centerServices)
    {
        $where = array_merge($this->request->param(), [
            'product_type' => 'virtual',
        ]);
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);
        $list = $centerServices->getAdminList($where, $page, $limit);
        return app('json')->success($list);
    }

    /**
     * 订单详情
     * 从中台获取完整订单数据，合并网盘商品扩展信息
     * @param string $orderNo
     * @param OrderCenterServices $centerServices
     * @param OrderServices $service
     * @return \think\Response
     */
    public function read(string $orderNo, OrderCenterServices $centerServices, OrderServices $service)
    {
        try {
            $detail = $centerServices->getDetailByOrderNo($orderNo);
            // 合并网盘商品扩展信息
            $extendInfo = $service->getExtendInfo($orderNo);
            if (!empty($extendInfo)) {
                $detail['virtual_extend'] = $extendInfo;
            }
            return app('json')->success($detail);
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
