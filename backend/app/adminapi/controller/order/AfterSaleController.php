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

namespace app\adminapi\controller\order;

use app\adminapi\controller\AuthController;
use app\services\order\AfterSaleServices;
use think\facade\App;

/**
 * 后台售后管理控制器
 * 商家审核售后申请、处理退货/退款/补发/维修等全流程操作
 * 售后规则由 AfterSaleServices 按产品类型 Handler 分流，控制器仅做参数校验
 */
class AfterSaleController extends AuthController
{
    /**
     * @var AfterSaleServices
     */
    protected $services;

    /**
     * 构造方法
     * @param App $app
     * @param AfterSaleServices $services 售后核心服务
     */
    public function __construct(App $app, AfterSaleServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 售后列表
     * 支持按售后单号/订单号/状态/类型/产品类型/时间范围筛选
     * @return mixed
     */
    public function getList()
    {
        $where = $this->request->getMore([
            ['after_sale_no', ''],
            ['order_no', ''],
            ['status', ''],
            ['type', ''],
            ['product_type', ''],
            ['order_category', ''],
            ['start_time', ''],
            ['end_time', ''],
            ['page', 1],
            ['limit', 15],
        ]);
        $page = max(1, (int)$where['page']);
        $limit = max(1, (int)$where['limit']);
        return app('json')->success($this->services->getListForAdmin($where, $page, $limit));
    }

    /**
     * 售后详情
     * 含售后单完整信息、时间线、操作按钮、审核信息、管理备注
     * @param int $id 售后单主键
     * @return mixed
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        $detail = $this->services->getDetailForAdmin($id);
        if (empty($detail)) {
            return app('json')->fail('售后单不存在');
        }
        return app('json')->success($detail);
    }

    /**
     * 审核售后
     * 商家同意或拒绝售后申请，拒绝时需填写拒绝原因
     * - 通过：按售后类型进入对应分支（仅退款→退款 / 退货退款→待退货 / 换货→待退货 / 补发→补发中 / 维修→待退货）
     * - 拒绝：售后单置 audit_rejected，中台订单状态恢复到售后前
     * @param int $id 售后单主键
     * @return mixed
     */
    public function audit(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        [$result, $rejectReason, $auditRemark] = $this->request->postMore([
            ['result', ''],
            ['reject_reason', ''],
            ['audit_remark', ''],
        ], true);
        if (!in_array($result, ['pass', 'reject'])) {
            return app('json')->fail('审核结果参数错误');
        }
        // 拒绝必须填写拒绝原因
        if ($result === 'reject' && $rejectReason === '') {
            return app('json')->fail('请填写拒绝原因');
        }
        $auditUid = (int)$this->adminId;
        try {
            $this->services->audit($id, $result, $auditUid, $rejectReason, $auditRemark);
            return app('json')->success('审核成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 商家确认收货
     * 用户退货并寄回后，商家确认收到退货商品，按售后类型分流后续流程：
     * - 退货退款 → 继续执行退款
     * - 换货 → 继续补发
     * - 维修 → 进入维修中
     * @param int $id 售后单主键
     * @return mixed
     */
    public function receive(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        try {
            $this->services->receive($id);
            return app('json')->success('确认收货成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 执行退款
     * 仅退款审核通过后直接退款，或退货退款确认收货后退款
     * @param int $id 售后单主键
     * @return mixed
     */
    public function refund(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        [$amount, $refundType, $refundTradeNo] = $this->request->postMore([
            ['amount', 0],
            ['refund_type', 'original'],
            ['refund_trade_no', ''],
        ], true);
        $amount = (float)$amount;
        if ($amount < 0) {
            return app('json')->fail('退款金额不能小于0');
        }
        try {
            $refund = $this->services->refund($id, $amount, [
                'refund_type'     => $refundType,
                'refund_trade_no' => $refundTradeNo,
            ]);
            $state = (string)($refund['state'] ?? '');
            $message = $state === \app\model\order\RefundOrder::STATE_COMPLETED
                ? '退款成功'
                : ($state === \app\model\order\RefundOrder::STATE_MANUAL_REVIEW ? '退款已转人工复核' : '退款已受理，正在处理中');
            return app('json')->success($message, [
                'refund_no' => $refund['refund_no'] ?? '',
                'state' => $state,
            ]);
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 商家补发商品
     * 换货/补发/维修完成后商家寄出商品，填写补发物流单号
     * @param int $id 售后单主键
     * @return mixed
     */
    public function resend(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        [$deliveryType, $deliveryNo] = $this->request->postMore([
            ['delivery_type', ''],
            ['delivery_no', ''],
        ], true);
        if ($deliveryType === '' || $deliveryNo === '') {
            return app('json')->fail('请填写快递公司和单号');
        }
        try {
            $this->services->resend($id, [
                'delivery_type' => $deliveryType,
                'delivery_no'   => $deliveryNo,
            ]);
            return app('json')->success('补发成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 开始维修
     * 维修类型售后，商家确认收到维修件后开始维修
     * @param int $id 售后单主键
     * @return mixed
     */
    public function startRepair(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        $record = $this->services->getById($id);
        if (empty($record)) {
            return app('json')->fail('售后单不存在');
        }
        if (($record['status'] ?? '') !== 'receiving') {
            return app('json')->fail('当前状态不可开始维修');
        }
        try {
            // 收货后维修类型自动进入 repairing，这里复用 receive 触发
            $this->services->receive($id);
            return app('json')->success('已开始维修');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 维修完成
     * 维修完成后商家寄回商品，进入补发流程
     * @param int $id 售后单主键
     * @return mixed
     */
    public function repairComplete(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        [$repairRemark] = $this->request->postMore([
            ['repair_remark', ''],
        ], true);
        try {
            $this->services->repairComplete($id, $repairRemark);
            return app('json')->success('维修完成');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 更新管理备注
     * @param int $id 售后单主键
     * @return mixed
     */
    public function remark(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        [$remark] = $this->request->postMore([
            ['remark', ''],
        ], true);
        try {
            $this->services->updateRemark($id, $remark);
            return app('json')->success('备注成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 查询售后物流进度
     * 支持退货物流(return)和补发物流(resend)两种类型查询，复用统一物流查询驱动
     * @param int $id 售后单主键
     * @return mixed
     */
    public function express(int $id)
    {
        if ($id <= 0) {
            return app('json')->fail('参数错误');
        }
        // type 区分查询退货物流还是补发物流
        [$type] = $this->request->getMore([
            ['type', 'return'],
        ], true);
        if (!in_array($type, ['return', 'resend'])) {
            return app('json')->fail('物流类型参数错误');
        }
        try {
            return app('json')->success($this->services->queryExpress($id, $type));
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
