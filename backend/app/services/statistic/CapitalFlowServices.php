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

namespace app\services\statistic;

use app\dao\system\statistics\CapitalFlowDao;
use crmeb\basic\BaseService;
use app\services\pay\PayServices;
use crmeb\exceptions\AdminException;

class CapitalFlowServices extends BaseService
{
    /**
     * @var CapitalFlowDao
     */
    protected $dao;

    /**
     * @param CapitalFlowDao $dao
     */
    public function __construct(CapitalFlowDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加资金流水
     * 资金流水记录的是平台真实资金进出（第三方在线收款、线下收银、充值及对应退款），
     * 余额支付、后台代付、0 元单等站内虚拟结算不产生现金流动，一律不记账。
     * 该判定放在此处作为唯一出口，避免各业务分支各自判断导致漏过滤。
     * @param array $orderInfo 业务订单信息
     * @param string $type 流水类型
     * @return bool 是否写入流水
     */
    public function setFlow($orderInfo, $type = '')
    {
        $data['flow_id'] = 'ZJ' . date('Ymdhis', time()) . rand('1000', '9999');
        switch ($type) {
            case 'order':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = $orderInfo['pay_price'];
                $data['trading_type'] = 1;
                $data['pay_type'] = $orderInfo['pay_type'];
                break;
            case 'refund':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = bcmul('-1', $orderInfo['refund_price'], 2);
                $data['trading_type'] = 2;
                $data['pay_type'] = $orderInfo['pay_type'];
                break;
            case 'recharge':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = $orderInfo['price'];
                $data['trading_type'] = 3;
                $data['pay_type'] = $orderInfo['recharge_type'];
                break;
            case 'refund_recharge':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = bcmul('-1', $orderInfo['price'], 2);
                $data['trading_type'] = 4;
                $data['pay_type'] = $orderInfo['recharge_type'];
                break;
            case 'luck':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = $orderInfo['price'];
                $data['trading_type'] = 5;
                $data['pay_type'] = $orderInfo['pay_type'];
                break;
            case 'extract':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = $orderInfo['price'];
                $data['trading_type'] = 6;
                $data['pay_type'] = $orderInfo['pay_type'];
                break;
            case 'pay_member':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = $orderInfo['pay_price'];
                $data['trading_type'] = 7;
                $data['pay_type'] = $orderInfo['pay_type'];
                break;
            case 'offline_scan':
                $data['order_id'] = $orderInfo['order_id'];
                $data['uid'] = $orderInfo['uid'];
                $data['price'] = $orderInfo['pay_price'];
                $data['trading_type'] = 8;
                $data['pay_type'] = $orderInfo['pay_type'];
                break;
            default:
                // 未知业务类型不记账，避免写入无金额无单号的空流水
                return false;
        }
        // 非现金渠道（余额支付/后台代付/0 元单等）无真实资金流，不记流水
        /** @var PayServices $payServices */
        $payServices = app()->make(PayServices::class);
        if (!$payServices->isCashChannel((string)($data['pay_type'] ?? ''))) {
            return false;
        }
        $data['nickname'] = $orderInfo['nickname'] ?? '';
        $data['phone'] = $orderInfo['phone'] ?? '';
        $data['add_time'] = time();
        return (bool)$this->dao->save($data);
    }

    /**
     * 获取资金流水
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFlowList($where)
    {
        $export = $where['export'] ?? 0;
        unset($where['export']);
        // 资金流水只展示现金渠道：余额支付、后台代付、0 元单等站内结算即使有历史数据也不展示
        $where['cash_only'] = 1;
        [$page, $limit] = $this->getPageValue();
        // 全量类型文案（用于列表/导出展示，历史数据可能包含已下线的类型）
        $typeLabels = [0 => '全部', 1 => '订单支付', 2 => '订单退款', 3 => '充值订单', 4 => '充值退款', 5 => '抽奖红包', 6 => '佣金提现', 7 => '购买会员', 8 => '线下收银'];
        // 筛选下拉只保留资金流水页面实际使用的现金交易类型；value 与 trading_type 一一对应，不依赖数组下标
        $type_options = [
            ['label' => '全部', 'value' => 0],
            ['label' => '订单支付', 'value' => 1],
            ['label' => '订单退款', 'value' => 2],
            ['label' => '购买会员', 'value' => 7],
            ['label' => '线下收银', 'value' => 8],
        ];
        // 兼容旧前端：status 为下标即 trading_type 的全量类型列表；新前端改用 type_options
        $status = ['全部', '订单支付', '订单退款', '充值订单', '充值退款', '抽奖红包', '佣金提现', '购买会员', '线下收银'];
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['trading_type'] = $typeLabels[$item['trading_type']] ?? '其他';
            $item['pay_type_name'] = PayServices::getPayType()[$item['pay_type'] != 'routine' ? $item['pay_type'] : 'weixin'] ?? '其他方式';
        }
        $count = $this->dao->count($where);
        if ($export) {
            $fileKey = ['flow_id', 'order_id', 'nickname', 'phone', 'price', 'trading_type', 'pay_type_name', 'add_time', 'mark'];
            $header = ['交易单号', '关联订单', '用户', '电话', '金额', '订单类型', '支付类型', '交易时间', '备注'];
            $fileName = '账单导出' . date('YmdHis') . rand(1000, 9999);
            return compact('list', 'fileKey', 'header', 'fileName');
        } else {
            return compact('list', 'count', 'status', 'type_options');
        }
    }

    /**
     * 添加备注
     * @param $id
     * @param $data
     * @return bool
     */
    public function setMark($id, $data)
    {
        $res = $this->dao->update($id, $data);
        if ($res) {
            return true;
        } else {
            throw new AdminException('备注失败');
        }
    }

    /**
     * 获取账单记录
     * @param $where
     * @return array
     */
    public function getFlowRecord($where)
    {
        // 账单统计与流水列表口径保持一致，只统计现金渠道
        $where['cash_only'] = 1;
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getRecordList($where, $page, $limit);
        $i = 1;
        foreach ($data['list'] as &$item) {
            $item['id'] = $i;
            $i++;
            $item['entry_price'] = bcadd($item['income_price'], $item['exp_price'], 2);
            switch ($where['type']) {
                case "day" :
                    $item['title'] = "日账单";
                    $item['add_time'] = date('Y-m-d', $item['add_time']);
                    break;
                case "week" :
                    $item['title'] = "周账单";
                    $item['add_time'] = '第' . $item['day'] . '周(' . date('m', $item['add_time']) . '月)';
                    break;
                case "month" :
                    $item['title'] = "月账单";
                    $item['add_time'] = date('Y-m', $item['add_time']);
                    break;
            }
        }
        return $data;
    }
}
