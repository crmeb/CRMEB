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

namespace app\services\statistic;

use app\dao\system\statistics\CapitalFlowDao;
use app\services\BaseServices;
use app\services\pay\PayServices;
use crmeb\exceptions\AdminException;

class CapitalFlowServices extends BaseServices
{
    /**
     * @param CapitalFlowDao $dao
     */
    public function __construct(CapitalFlowDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加资金流水
     * @param $orderInfo
     * @param string $type
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
                break;
        }
        $data['nickname'] = $orderInfo['nickname'];
        $data['phone'] = $orderInfo['phone'];
        $data['add_time'] = time();
        $this->dao->save($data);
    }

    /**
     * 获取资金流水
     * @param $where
     * @return array
     */
    public function getFlowList($where)
    {
        $export = $where['export'] ?? 0;
        unset($where['export']);
        [$page, $limit] = $this->getPageValue();
        $status = ['全部', '支付订单', '订单退款', '充值订单', '充值退款', '抽奖红包', '佣金提现', '购买会员', '线下收银'];
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['trading_type'] = $status[$item['trading_type']];
            $item['pay_type_name'] = PayServices::PAY_TYPE[$item['pay_type'] != 'routine' ? $item['pay_type'] : 'weixin'];
        }
        $count = $this->dao->count($where);
        if ($export) {
            $fileKey = ['flow_id', 'order_id', 'nickname', 'phone', 'price', 'trading_type', 'pay_type_name', 'add_time', 'mark'];
            $header = ['交易单号', '关联订单', '用户', '电话', '金额', '订单类型', '支付类型', '交易时间', '备注'];
            $fileName = '账单导出' . date('YmdHis') . rand(1000, 9999);
            return compact('list', 'fileKey', 'header', 'fileName');
        } else {
            return compact('list', 'count', 'status');
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
            throw new AdminException(100025);
        }
    }

    /**
     * 获取账单记录
     * @param $where
     * @return array
     */
    public function getFlowRecord($where)
    {
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
