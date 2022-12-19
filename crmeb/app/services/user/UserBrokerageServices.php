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

namespace app\services\user;

use app\dao\user\UserBrokerageDao;
use app\services\BaseServices;
use crmeb\exceptions\ApiException;

/**
 * 用户佣金
 * Class UserBrokerageServices
 * @package app\services\user
 * @method getUserFrozenPrice(int $uid) 获取某个用户冻结的佣金
 */
class UserBrokerageServices extends BaseServices
{
    /**
     * 用户记录模板
     * @var array[]
     */
    protected $incomeData = [
        'get_self_brokerage' => [
            'title' => '获得自购订单佣金',
            'type' => 'self_brokerage',
            'mark' => '您成功消费{%pay_price%}元,奖励自购佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'get_brokerage' => [
            'title' => '获得下级推广订单佣金',
            'type' => 'one_brokerage',
            'mark' => '{%nickname%}成功消费{%pay_price%}元,奖励推广佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'get_two_brokerage' => [
            'title' => '获得二级推广订单佣金',
            'type' => 'two_brokerage',
            'mark' => '二级推广人{%nickname%}成功消费{%pay_price%}元,奖励推广佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'get_user_brokerage' => [
            'title' => '获得推广用户佣金',
            'type' => 'brokerage_user',
            'mark' => '成功推广用户：{%nickname%},奖励推广佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'extract' => [
            'title' => '佣金提现',
            'type' => 'extract',
            'mark' => '{%mark%}',
            'status' => 1,
            'pm' => 0
        ],
        'extract_fail' => [
            'title' => '提现失败',
            'type' => 'extract_fail',
            'mark' => '提现失败,退回佣金{%number%}元',
            'status' => 1,
            'pm' => 1
        ],
        'brokerage_to_nowMoney' => [
            'title' => '佣金提现到余额',
            'type' => 'extract_money',
            'mark' => '佣金提现到余额{%number%}元',
            'status' => 1,
            'pm' => 0
        ],
        'brokerage_refund' => [
            'title' => '退款退佣金',
            'type' => 'refund',
            'mark' => '订单退款扣除佣金{%number%}元',
            'status' => 1,
            'pm' => 0
        ],
        'get_staff_brokerage' => [
            'title' => '获得员工推广订单佣金',
            'type' => 'staff_brokerage',
            'mark' => '{%nickname%}成功消费{%pay_price%}元,奖励推广佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'get_agent_brokerage' => [
            'title' => '获得代理推广订单佣金',
            'type' => 'agent_brokerage',
            'mark' => '{%nickname%}成功消费{%pay_price%}元,奖励推广佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'get_division_brokerage' => [
            'title' => '获得事业部推广订单佣金',
            'type' => 'division_brokerage',
            'mark' => '{%nickname%}成功消费{%pay_price%}元,奖励推广佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
        'get_pink_master_brokerage' => [
            'title' => '获得拼团团长佣金',
            'type' => 'pink_master_brokerage',
            'mark' => '开团成功，奖励团长佣金{%number%}',
            'status' => 1,
            'pm' => 1
        ],
    ];


    /**
     * UserBrokerageServices constructor.
     * @param UserBrokerageDao $dao
     */
    public function __construct(UserBrokerageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 写入佣金记录
     * @param string $type 写入类型
     * @param int $uid
     * @param int|string|array $number
     * @param int|string $balance
     * @param $linkId
     * @return bool|mixed
     */
    public function income(string $type, int $uid, $number, $balance, $linkId)
    {
        $data = $this->incomeData[$type] ?? null;
        if (!$data) {
            return true;
        }
        $data['uid'] = $uid;
        $data['balance'] = $balance ?? 0;
        $data['link_id'] = $linkId;
        if (is_array($number)) {
            $key = array_keys($number);
            $key = array_map(function ($item) {
                return '{%' . $item . '%}';
            }, $key);
            $value = array_values($number);
            $data['number'] = $number['number'] ?? 0;
            $data['frozen_time'] = $number['frozen_time'] ?? 0;
            $data['mark'] = str_replace($key, $value, $data['mark']);
        } else {
            $data['number'] = $number;
            $data['mark'] = str_replace(['{%number%}'], $number, $data['mark']);
        }
        $data['add_time'] = time();

        return $this->dao->save($data);
    }

    /**
     * 某个用户佣金总和
     * @param int $uid
     * @param array|string[] $type
     * @param string $time
     * @return float
     */
    public function getUserBrokerageSum(int $uid, array $type = ['one_brokerage', 'two_brokerage', 'brokerage_user'], $time = '')
    {
        $where = ['uid' => $uid];
        if ($type) $where['type'] = $type;
        if ($time) $where['time'] = $time;
        return $this->dao->sum($where, 'number', true);
    }

    /**
     * 退佣金
     * @param $order
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderRefundBrokerageBack($order)
    {
        $id = (int)$order['id'];
        $where = [
            'uid' => [$order['spread_uid'], $order['spread_two_uid'], $order['staff_id'], $order['agent_id'], $order['division_id']],
            'type' => ['self_brokerage', 'one_brokerage', 'two_brokerage', 'staff_brokerage', 'agent_brokerage', 'division_brokerage', 'pink_master_brokerage'],
            'link_id' => $id,
            'pm' => 1
        ];
        $brokerageList = $this->dao->getUserBrokerageList($where);
        //子订单
        if (!$brokerageList && $order['pid']) {
            $where['link_id'] = $order['pid'];
            $p_brokerageList = $this->dao->getUserBrokerageList($where);
            //主订单已分佣 子订单按订单拆分后计算结果回退
            if ($p_brokerageList) {
                $brokerageList = [
                    ['uid' => $order['spread_uid'], 'number' => $order['one_brokerage']],
                    ['uid' => $order['spread_two_uid'], 'number' => $order['two_brokerage']],
                ];
            }
        }
        $res = true;
        if ($brokerageList) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $brokerages = $userServices->getColumn([['uid', 'in', array_column($brokerageList, 'uid')]], 'brokerage_price', 'uid');
            $brokerageData = [];

            foreach ($brokerageList as $item) {
                if (!$item['uid']) continue;
                $usermoney = $brokerages[$item['uid']] ?? 0;
                if ($item['number'] > $usermoney) {
                    $item['number'] = $usermoney;
                }
                $res = $res && $userServices->bcDec($item['uid'], 'brokerage_price', (string)$item['number'], 'uid');
                $brokerageData[] = [
                    'title' => '退款退佣金',
                    'uid' => $item['uid'],
                    'pm' => 0,
                    'add_time' => time(),
                    'type' => 'refund',
                    'number' => $item['number'],
                    'link_id' => $id,
                    'balance' => bcsub((string)$usermoney, (string)$item['number'], 2),
                    'mark' => '订单退款扣除佣金' . floatval($item['number']) . '元'
                ];
            }
            if ($brokerageData) {
                $res = $res && $this->dao->saveAll($brokerageData);
            }
            //修改佣金冻结时间
            $this->dao->update($where, ['frozen_time' => 0]);
        }
        return $res;
    }

    /**
     * 佣金排行
     * @param string $time
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function brokerageRankList(string $time = 'week')
    {
        $where = [];
        if ($time) {
            $where['time'] = $time;
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->brokerageRankList($where, $page, $limit);
        foreach ($list as $key => &$item) {
            if (!isset($item['user']) || !$item['user'] || $item['brokerage_price'] <= 0) {
                unset($list[$key]);
                continue;
            }
            $item['nickname'] = $item['user']['nickname'] ?? '';
            $item['avatar'] = $item['user']['avatar'] ?? '';
            if ($item['brokerage_price'] == '0.00' || $item['brokerage_price'] == 0 || !$item['brokerage_price']) {
                unset($list[$key]);
            }
            unset($item['user']);
        }
        return array_merge($list);
    }

    /**
     * 获取用户排名
     * @param int $uid
     * @param string $time
     */
    public function getUserBrokerageRank(int $uid, string $time = 'week')
    {
        $where = [];
        if ($time) {
            $where['time'] = $time;
        }
        $list = $this->dao->brokerageRankList($where);
        foreach ($list as $key => &$item) {
            if (!isset($item['user']) || !$item['user'] || $item['brokerage_price'] <= 0) {
                unset($list[$key]);
            }
        }
        $position_tmp_one = array_column($list, 'uid');
        $position_tmp_two = array_column($list, 'brokerage_price', 'uid');
        if (!in_array($uid, $position_tmp_one)) {
            $position = 0;
        } else {
            if ($position_tmp_two[$uid] == 0.00) {
                $position = 0;
            } else {
                $position = array_search($uid, $position_tmp_one) + 1;
            }
        }
        return $position;
    }

    /**
     * 推广数据    昨天的佣金   累计提现金额  当前佣金
     * @param int $uid
     * @return mixed
     */
    public function commission(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userServices->getUserInfo($uid)) {
            throw new ApiException(100026);
        }
        /** @var UserExtractServices $userExtract */
        $userExtract = app()->make(UserExtractServices::class);
        $data = [];
        $data['uid'] = $uid;
        $data['pm'] = 1;
        $data['commissionSum'] = $this->getUsersBokerageSum($data);
        $data['pm'] = 0;
        $data['commissionRefund'] = $this->getUsersBokerageSum($data);
        $data['commissionCount'] = $data['commissionSum'] > $data['commissionRefund'] ? bcsub((string)$data['commissionSum'], (string)$data['commissionRefund'], 2) : 0.00;
        $data['lastDayCount'] = $this->getUsersBokerageSum($data, 'yesterday');//昨天的佣金
        $data['extractCount'] = $userExtract->getUserExtract($uid);//累计提现金额
        return $data;
    }

    /**
     * 计算佣金
     * @param array $where
     * @param int $time
     * @return mixed
     */
    public function getUsersBokerageSum(array $where, $time = 0)
    {
        $where_data = [
            'status' => 1,
            'pm' => $where['pm'] ?? '',
            'uid' => $where['uid'] ?? '',
            'time' => $where['time'] ?? 0
        ];
        if ($time) $where_data['time'] = $time;
        return $this->dao->getBrokerageSumColumn($where_data);
    }

    /**
     * 佣金明细
     * @param $uid
     * @param $type
     * @return array
     */
    public function getBrokerageList($uid, $type)
    {
        $where = [];
        $where['uid'] = $uid;
        [$page, $limit] = $this->getPageValue();
        if ($type == 4) {
            $where['type'] = ['extract', 'extract_money', 'extract_fail'];
        }
        /** @var UserExtractServices $userExtractService */
        $userExtractService = app()->make(UserExtractServices::class);
        $userExtract = $userExtractService->getColumn(['uid' => $uid], 'fail_msg', 'id');
        $list = $this->dao->getList($where, '*', $page, $limit);
        $count = $this->dao->count($where);
        $times = [];
        if ($list) {
            foreach ($list as &$item) {
                $item['time'] = $item['time_key'] = $item['add_time'] ? date('Y-m', (int)$item['add_time']) : '';
                $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i', (int)$item['add_time']) : '';
                $item['fail_msg'] = $item['type'] == 'extract_fail' ? $userExtract[$item['link_id']] : '';
            }
            $times = array_merge(array_unique(array_column($list, 'time_key')));
        }
        return ['list' => $list, 'time' => $times, 'count' => $count];
    }

    /**
     * 前端佣金排行页面数据
     * @param int $uid
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function brokerageRank(int $uid, $type)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        if (!$userService->getUserInfo($uid)) {
            throw new ApiException(100026);
        }
        return [
            'rank' => $this->brokerageRankList($type),
            'position' => $this->getUserBrokerageRank($uid, $type)
        ];
    }
}
