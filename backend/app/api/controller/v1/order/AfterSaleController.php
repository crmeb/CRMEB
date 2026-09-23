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

namespace app\api\controller\v1\order;

use app\Request;
use app\dao\order\AfterSaleDao;
use app\services\order\AfterSaleServices;
use app\services\order\OrderCenterServices;

/**
 * 用户端售后控制器
 * 负责售后可用类型查询、售后申请、取消售后、填写退货物流、售后列表等用户端操作
 * 售后规则由 AfterSaleServices 按产品类型 getAfterSaleRule() 三维规则分流，控制器仅做参数校验与归属校验
 */
class AfterSaleController
{
    /**
     * @var AfterSaleServices
     */
    protected AfterSaleServices $services;

    /**
     * @var AfterSaleDao
     */
    protected AfterSaleDao $dao;

    /**
     * @var OrderCenterServices
     */
    protected OrderCenterServices $centerServices;

    /**
     * 构造方法
     * @param AfterSaleServices $services 售后核心服务
     * @param AfterSaleDao $dao 售后单 DAO
     * @param OrderCenterServices $centerServices 中台订单服务
     */
    public function __construct(AfterSaleServices $services, AfterSaleDao $dao, OrderCenterServices $centerServices)
    {
        $this->services = $services;
        $this->dao = $dao;
        $this->centerServices = $centerServices;
    }

    /**
     * 获取订单可用售后类型
     * 经三维规则（订单大类 × 产品类型 × 活动类型）合并后返回当前订单可用的售后类型
     * @param Request $request
     * @param string $orderNo 订单号
     * @return mixed
     */
    public function availableTypes(Request $request, string $orderNo)
    {
        if ($orderNo === '') {
            return app('json')->fail('参数错误');
        }
        $center = $this->centerServices->getOne(['order_no' => $orderNo]);
        $center = is_object($center) ? $center->toArray() : $center;
        // 校验订单归属当前用户
        if (empty($center) || (int)($center['uid'] ?? 0) !== (int)$request->uid()) {
            return app('json')->fail('订单不存在');
        }
        return app('json')->success($this->services->getAvailableTypes($orderNo));
    }

    /**
     * 用户提交售后申请
     * 售后作为订单状态处理：申请后中台订单进入 after_sale（售后中）
     * @param Request $request
     * @return mixed
     */
    public function apply(Request $request)
    {
        $data = $request->postMore([
            ['order_no', ''],
            ['type', ''],
            ['refund_price', 0.00],
            ['refund_integral', 0], // 积分退款标的：退还积分数
            ['reason', ''],
            ['evidence_images', []],
            ['product_snapshot', []],
            ['items', []], // v8.1.1 新增：商品级售后范围 [{cart_id, refund_num}]
        ]);
        if ($data['order_no'] === '' || $data['type'] === '') {
            return app('json')->fail('参数错误');
        }
        $uid = (int)$request->uid();
        $center = $this->centerServices->getOne(['order_no' => $data['order_no']]);
        $center = is_object($center) ? $center->toArray() : $center;
        // 校验订单归属当前用户
        if (empty($center) || (int)($center['uid'] ?? 0) !== $uid) {
            return app('json')->fail('订单不存在');
        }
        $data['uid'] = $uid;
        try {
            $res = $this->services->apply($data);
            return app('json')->success([
                'after_sale_no' => $res['after_sale_no'] ?? '',
            ]);
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 用户取消售后申请
     * 仅待审核/审核通过状态可取消，取消后中台订单状态恢复为售后前状态
     * @param Request $request
     * @return mixed
     */
    public function cancel(Request $request)
    {
        [$afterSaleNo] = $request->postMore([
            ['after_sale_no', ''],
        ], true);
        if ($afterSaleNo === '') {
            return app('json')->fail('参数错误');
        }
        $record = $this->getOwnedAfterSale($afterSaleNo, (int)$request->uid());
        if (empty($record)) {
            return app('json')->fail('售后单不存在');
        }
        try {
            $this->services->cancel((int)$record['id']);
            return app('json')->success('取消成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 用户填写退货物流
     * 售后类型为 return_refund/exchange/repair 且审核通过后，用户寄回商品填写物流单号
     * @param Request $request
     * @return mixed
     */
    public function deliverReturn(Request $request)
    {
        [$afterSaleNo, $deliveryType, $deliveryNo, $images] = $request->postMore([
            ['after_sale_no', ''],
            ['delivery_type', ''],
            ['delivery_no', ''],
            ['images', []],
        ], true);
        if ($afterSaleNo === '' || $deliveryType === '' || $deliveryNo === '') {
            return app('json')->fail('参数错误');
        }
        $record = $this->getOwnedAfterSale($afterSaleNo, (int)$request->uid());
        if (empty($record)) {
            return app('json')->fail('售后单不存在');
        }
        try {
            $this->services->returnGoods((int)$record['id'], [
                'delivery_type' => $deliveryType,
                'delivery_no'   => $deliveryNo,
                'images'        => is_array($images) ? $images : [],
            ]);
            return app('json')->success('提交成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取售后申请表单配置
     * 按产品类型 Handler 返回动态字段，前端据此渲染申请表单（数据驱动）
     * @param Request $request
     * @param string $orderNo 订单号
     * @param string $type 售后类型
     * @return mixed
     */
    public function formConfig(Request $request, string $orderNo, string $type)
    {
        if ($orderNo === '' || $type === '') {
            return app('json')->fail('参数错误');
        }
        // 校验订单归属当前用户
        $center = $this->centerServices->getOne(['order_no' => $orderNo]);
        $center = is_object($center) ? $center->toArray() : $center;
        if (empty($center) || (int)($center['uid'] ?? 0) !== (int)$request->uid()) {
            return app('json')->fail('订单不存在');
        }
        return app('json')->success($this->services->getFormConfig($orderNo, $type));
    }

    /**
     * 获取售后详情
     * 含售后单信息、时间线、用户可操作按钮，前端详情模式据此渲染
     * @param Request $request
     * @param string $afterSaleNo 售后单号
     * @return mixed
     */
    public function detail(Request $request, string $afterSaleNo)
    {
        if ($afterSaleNo === '') {
            return app('json')->fail('参数错误');
        }
        // 校验售后单归属当前用户
        $record = $this->getOwnedAfterSale($afterSaleNo, (int)$request->uid());
        if (empty($record)) {
            return app('json')->fail('售后单不存在');
        }
        return app('json')->success($this->services->getDetail($afterSaleNo));
    }

    /**
     * 用户确认收货
     * 补发/维修完成后用户确认收到商品，售后完结
     * @param Request $request
     * @return mixed
     */
    public function confirmReceive(Request $request)
    {
        [$afterSaleNo] = $request->postMore([
            ['after_sale_no', ''],
        ], true);
        if ($afterSaleNo === '') {
            return app('json')->fail('参数错误');
        }
        $record = $this->getOwnedAfterSale($afterSaleNo, (int)$request->uid());
        if (empty($record)) {
            return app('json')->fail('售后单不存在');
        }
        try {
            $this->services->confirmReceive((int)$record['id']);
            return app('json')->success('确认成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 用户售后列表
     * 支持 status_group 状态分组筛选：空=全部，processing=处理中，completed=已完成，canceled=已取消
     * 返回 {list, count} 结构，列表项含 type_text/status_text 文案
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        [$page, $limit, $statusGroup] = $request->getMore([
            ['page', 1],
            ['limit', 10],
            ['status_group', ''],
        ], true);
        $result = $this->services->getListForUser(
            (int)$request->uid(),
            (int)$page,
            (int)$limit,
            (string)$statusGroup
        );
        return app('json')->success($result);
    }

    /**
     * 获取当前用户拥有的售后单
     * 按售后单号查询并校验归属，防止越权操作他人售后单
     * @param string $afterSaleNo 售后单号
     * @param int $uid 用户ID
     * @return array|null
     */
    protected function getOwnedAfterSale(string $afterSaleNo, int $uid): ?array
    {
        $record = $this->dao->getByNo($afterSaleNo);
        if (empty($record) || (int)$record['uid'] !== $uid) {
            return null;
        }
        return $record;
    }
}
