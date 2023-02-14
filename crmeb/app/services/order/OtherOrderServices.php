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

namespace app\services\order;


use app\dao\order\OtherOrderDao;
use app\services\BaseServices;
use app\services\pay\PayServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\member\MemberShipServices;
use app\services\user\UserBillServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserServices;
use app\services\user\member\MemberCardServices;
use crmeb\exceptions\ApiException;
use think\App;
use app\jobs\OtherOrderJob;

/**
 * Class OtherOrderServices
 * @package app\services\order
 * @method getDistinctCount(array $where, $field, ?bool $search = true)
 * @method getPayUserCount(int $time, string $channel_type)
 * @method getTrendData($time, $type, $timeType)
 */
class OtherOrderServices extends BaseServices
{

    /**
     * 初始化，获得dao层句柄
     * OtherOrderServices constructor.
     * @param OtherOrderDao $dao
     */
    public function __construct(OtherOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 生成会员购买订单数据
     * @param array $data
     * @return mixed
     */
    public function addOtherOrderData(array $data)
    {
        if (!$data) throw new ApiException(100026);
        $add = [
            'uid' => $data['uid'],
            'type' => $data['type'] ?? 1,
            'order_id' => $data['order_id'],
            'channel_type' => $data['channel_type'],
            'pay_type' => $data['pay_type'] ?? 0,
            'member_type' => $data['member_type'] ?? 0,
            'member_price' => $data['member_price'] ?? 0.00,
            'pay_price' => $data['pay_price'] ?? 0.00,
            'code' => $data['member_code'] ?? "",
            'vip_day' => $data['vip_day'] ?? 0,
            'is_permanent' => $data['is_permanent'] ?? 0,
            'is_free' => $data['is_free'] ?? 0,
            'overdue_time' => $data['overdue_time'] ?? 0,
            'status' => 0,
            'paid' => $data['paid'] ?? 0,
            'pay_time' => $data['pay_time'] ?? 0,
            'money' => $data['money'] ?? 0,
            'add_time' => time(),
        ];
        return $this->dao->save($add);
    }

    /**
     * 能否领取免费
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isCanGetFree(int $uid)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        /** @var StoreOrderEconomizeServices $economizeService */
        $economizeService = app()->make(StoreOrderEconomizeServices::class);
        $freeDay = $memberShipService->getVipDay(['type' => "free"]);
        $freeConfig = array();
        $freeConfig['price'] = 0;
        $freeConfig['pre_price'] = 0;
        $freeConfig['title'] = "免费会员";
        $freeConfig['type'] = "free";
        $freeConfig['vip_day'] = $freeDay ?: 0;
        $userInfo = $userService->get($uid);
        if ($freeConfig) {
            $freeConfig['is_record'] = 0;
            $record = $this->dao->getOneByWhere(['uid' => $uid, 'is_free' => 1]);
            if ($record) {
                $freeConfig['is_record'] = 1;

            }
        }
        $registerTime = $this->TimeConvert(['start_time' => date('Y-m-d H:i:s', $userInfo['add_time']), 'end_time' => date('Y-m-d H:i:s', time())]);
        $userInfo['register_days'] = $registerTime['days'];
        $userInfo['economize_money'] = $economizeService->sumEconomizeMoney($uid);
        $userInfo['shop_name'] = sys_config('site_name');
        $freeConfig['user_info'] = $userInfo;
        return $freeConfig;
    }

    /**搜索时间转换
     * @param $timeKey
     * @param bool $isNum
     * @throws \Exception
     */
    public function TimeConvert($timeKey, $isNum = false)
    {
        switch ($timeKey) {
            case "today" :
                $data['start_time'] = date('Y-m-d 00:00:00', time());
                $data['end_time'] = date('Y-m-d 23:59:59', time());
                $data['days'] = 1;
                break;
            case "yestoday" :
                $data['start_time'] = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $data['end_time'] = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $data['days'] = 1;
                break;
            case "last_month" :
                $data['start_time'] = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $data['end_time'] = date('Y-m-t 23:59:59', strtotime('-1 month'));
                $data['days'] = 30;
                break;
            case "month" :
                $data['start_time'] = $month_start_time = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
                $data['end_time'] = date('Y-m-d 23:59:59', strtotime("$month_start_time +1 month -1 day"));
                $data['days'] = 30;
                break;
            case "year" :
                $data['start_time'] = date('Y-01-01 00:00:00', time());
                $data['end_time'] = date('Y-12-t 23:59:59', time());
                $data['days'] = 365;
                break;
            case "last_year" :
                $data['start_time'] = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $data['end_time'] = date('Y-12-t 23:59:59', strtotime('-1 year'));
                $data['days'] = 365;
                break;
            case 30 :
            case 15 :
            case 7 :
                if (!$isNum) {
                    $data['start_time'] = date("Y-m-d 00:00:00", strtotime("-$timeKey day"));
                    $data['end_time'] = date('Y-m-d 23:59:59', time());
                    $data['days'] = $timeKey;
                } else {
                    $day = $timeKey * 2;
                    $data['start_time'] = date("Y-m-d 00:00:00", strtotime("-$day  day"));
                    $data['end_time'] = date("Y-m-d 23:59:59", strtotime("-$timeKey day"));
                    $data['days'] = $timeKey;
                }
                break;
            default:
                $datetime_start = new \DateTime($timeKey['start_time']);
                $datetime_end = new \DateTime($timeKey['end_time']);
                $days = $datetime_start->diff($datetime_end)->days;
                $days = $days > 0 ? $days : 1;
                if (!$isNum) {
                    $data['start_time'] = $timeKey['start_time'];
                    $data['end_time'] = $timeKey['end_time'];
                    $data['days'] = $days;
                } else {
                    $data['start_time'] = date("Y-m-d 00:00:00", strtotime("-$days day"));
                    $data['end_time'] = $timeKey['start_time'];
                    $data['days'] = $days;
                }

        }
        return $data;
    }


    /**
     * 查询会员卡订单数据
     * @param array $where
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne(array $where, string $field = '*')
    {
        return $this->dao->getOne($where, $field);
    }

    /**
     * @param int $uid
     * @param string $channelType 支付渠道
     * @param bool $memberType 会员卡类型
     * @param string $payPrice 支付金额
     * @param string $payType 支付方式
     * @param $type 订单类型
     * @return mixed
     * @throws \Exception
     */
    public function createOrder(int $uid, string $channelType, $memberType = false, string $payPrice, string $payType, $type, $money, $mcId)
    {
        /** @var StoreOrderCreateServices $storeOrderCreateService */
        $storeOrderCreateService = app()->make(StoreOrderCreateServices::class);
        $orderInfo = [
            'uid' => $uid,
            'order_id' => $storeOrderCreateService->getNewOrderId('hy'),
            'pay_price' => $payPrice,
            'pay_type' => $payType,
            'channel_type' => $channelType,
            'member_code' => "",
        ];
        if ($type != 3) { //区别 0：免费领取会员 1：购买会员  2：卡密领取会员  3：线下付款
            if (!$memberType) throw new ApiException(410228);
            list($memberPrice, $isFree, $isPermanent, $overdueTime, $type, $newMemberRight) = $this->checkPayMemberType($memberType, $payPrice, $type, $uid, $mcId);
            $orderInfo['member_price'] = $memberPrice;
            $orderInfo['money'] = $memberPrice;
            $orderInfo['vip_day'] = $newMemberRight[$mcId]['vip_day'];
            $orderInfo['member_type'] = $memberType;
            $orderInfo['overdue_time'] = $overdueTime;
            $orderInfo['is_permanent'] = $isPermanent;
            $orderInfo['is_free'] = $isFree;
            $orderInfo['type'] = $type;
            $changeType = "create_member_order";
        } else {
            $orderInfo['type'] = $type;
            $orderInfo['member_code'] = "";
            $changeType = "create_offline_scan_order";
            $orderInfo['money'] = $money ?: $payPrice;
        }
        $memberOrder = $this->addOtherOrderData($orderInfo);
        if (!$memberOrder) {
            throw new ApiException(410200);
        }
        /** @var OtherOrderStatusServices $statusService */
        $statusService = app()->make(OtherOrderStatusServices::class);
        $statusService->save([
            'oid' => $memberOrder['id'],
            'change_type' => $changeType,
            'change_message' => '订单生成',
            'change_time' => time(),
            'shop_type' => $type,
        ]);
        return $memberOrder;
    }

    /**
     * 免费卡领取支付
     * @param $orderInfo
     * @return bool
     */
    public function zeroYuanPayment($orderInfo)
    {
        if ($orderInfo['paid']) {
            throw new ApiException(410174);
        }
        if ($orderInfo['member_type'] != 'free') {
            throw new ApiException(410216);
        }
        $res = $this->paySuccess($orderInfo, 'yue');//余额支付成功
        return $res;

    }

    /**
     * 会员卡支付成功
     * @param array $orderInfo
     * @param string $paytype
     * @return bool
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        /** @var OtherOrderStatusServices $statusService */
        $statusService = app()->make(OtherOrderStatusServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $type = 'pay_member';
        $res1 = true;
        switch ($orderInfo['type']) {
            case 0 :
            case 1 :
            case 2 :
                $type = "pay_member";
                $res1 = $userServices->setMemberOverdueTime($orderInfo['vip_day'], $orderInfo['uid'], 1, $orderInfo['member_type']);
                break;
            case 3:
                $type = "offline_scan";
                $res1 = true;
                break;
        }
        if ($paytype == PayServices::ALIAPY_PAY && isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }
        $updata['paid'] = 1;
        $updata['pay_type'] = $paytype;
        $updata['pay_time'] = time();
        $orderInfo['pay_time'] = $updata['pay_time'];
        $res2 = $this->dao->update($orderInfo['id'], $updata);
        $res3 = $statusService->save([
            'oid' => $orderInfo['id'],
            'change_type' => 'pay_success',
            'change_message' => '用户付款成功',
            'shop_type' => $orderInfo['type'],
            'change_time' => time()
        ]);

        $now_money = $userServices->value(['uid' => $orderInfo['uid']], 'now_money');
        $res4 = $userBillServices->income($type, $orderInfo['uid'], $orderInfo['pay_price'], $now_money, $orderInfo['id']);
        //支付成功后发送消息
        OtherOrderJob::dispatch([$orderInfo]);
        $orderInfo['is_channel'] = 2;
        $orderInfo['total_num'] = 1;

        if ($orderInfo['pay_type'] != 'yue') {
            /** @var CapitalFlowServices $capitalFlowServices */
            $capitalFlowServices = app()->make(CapitalFlowServices::class);
            $userInfo = $userServices->get($orderInfo['uid']);
            $orderInfo['nickname'] = $userInfo['nickname'];
            $orderInfo['phone'] = $userInfo['phone'];
            $capitalFlowServices->setFlow($orderInfo, $type);
        }
        $res = $res1 && $res2 && $res3 && $res4;

        //购买付费会员返佣设置
        if (sys_config('member_brokerage', 0) == 1 && sys_config('brokerage_func_status', 0) == 1) {
            $spread_one = sys_config('is_self_brokerage') ? $orderInfo['uid'] : $userServices->getSpreadUid($orderInfo['uid']);
            $spread_two = sys_config('brokerage_level', 2) == 2 ? $userServices->getSpreadUid($spread_one) : 0;
            $spread_one_price = bcmul((string)$orderInfo['pay_price'], (string)bcdiv((string)sys_config('store_brokerage_ratio', 0), '100', 4), 2);
            $spread_two_price = bcmul((string)$orderInfo['pay_price'], (string)bcdiv((string)sys_config('store_brokerage_two', 0), '100', 4), 2);
            if ($spread_one && $spread_one_price > 0) $this->memberBrokerage($spread_one, $spread_one_price, sys_config('is_self_brokerage') ? 'get_self_brokerage' : 'get_brokerage', $orderInfo);
            if ($spread_two && $spread_two_price > 0) $this->memberBrokerage($spread_two, $spread_two_price, 'get_two_brokerage', $orderInfo);
        }

        return false !== $res;
    }

    /**
     * 购买付费会员返佣
     * @param $uid
     * @param $price
     * @param $type
     * @param $orderInfo
     */
    public function memberBrokerage($uid, $price, $type, $orderInfo)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($uid);
        // 上级推广员返佣之后的金额
        $balance = bcadd($userInfo['brokerage_price'], $price, 2);
        // 添加用户佣金
        $res1 = $userServices->bcInc($uid, 'brokerage_price', $price, 'uid');
        if ($res1) {
            //冻结时间
            $broken_time = intval(sys_config('extract_time'));
            $frozen_time = time() + $broken_time * 86400;
            // 添加佣金记录
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $userBrokerageServices->income($type, $uid, [
                'nickname' => $userInfo['nickname'],
                'pay_price' => floatval($price),
                'number' => floatval($userInfo['brokerage_price']),
                'frozen_time' => $frozen_time
            ], $balance, $orderInfo['id']);
        }
    }

    /**
     * 修改
     * @param $where
     * @param array $data
     * @return \crmeb\basic\BaseModel
     */
    public function update($where, array $data)
    {
        return $this->dao->update($where, $data);
    }

    /**
     * 购买会员卡数据校验
     * @param string $memberType
     * @param string $payPrice
     * @param string $type
     * @param $uid
     * @param $mcId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkPayMemberType(string $memberType, string $payPrice, string $type, $uid, $mcId)
    {
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($uid);
        if ($userInfo['is_money_level'] > 0 && $userInfo['is_ever_level'] > 0) throw new ApiException(410229);
        $newMemberRight = $memberCardService->getMemberTypeValue();
        if (!array_key_exists($mcId, $newMemberRight)) throw new ApiException(410230);
        $price = $newMemberRight[$mcId]['pre_price'];
        if ($payPrice != $price || ($memberType != 'free' && $payPrice <= 0)) throw new ApiException(100100);
        if ($memberType == 'free' && $newMemberRight[$mcId]['vip_day'] <= 0) throw new ApiException(100100);
        switch ($memberType) {
            case "free"://免费会员
                $isCanGetFree = $this->isCanGetFree($uid);
                if ($isCanGetFree['is_record'] == 1) throw new ApiException(410231);
                $memberPrice = 0.00; //会员卡价格
                $isFree = 1;//代表免费
                $isPermanent = 0;//代表非永久
                $overdueTime = bcadd(bcmul(abs($newMemberRight[$mcId]['vip_day']), "86400", 0), time(), 0);
                break;
            case "month":
            case "year":
            case "quarter":
            case "owner":
                $memberPrice = $price;
                $isFree = 0;
                $isPermanent = 0;
                $overdueTime = bcadd(bcmul(abs($newMemberRight[$mcId]['vip_day']), '86400', 0), time(), 0);
                break;
            case "ever":
                $memberPrice = $price;
                $isFree = 0;
                $isPermanent = 1;
                $overdueTime = -1;
                break;
            default:
                throw new ApiException(410232);
        }
        //return compact('member_price', 'is_free', 'is_permanent', 'overdue_time', 'type');
        return [$memberPrice, $isFree, $isPermanent, $overdueTime, $type, $newMemberRight];
    }

    /**
     * 根据查询用户购买会员金额
     * @param array $where
     * @return mixed
     */
    public function getMemberMoneyByWhere(array $where, string $sumField, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum" :
                return $this->dao->getWhereSumField($where, $sumField);
            case "group" :
                return $this->dao->getGroupField($where, $sumField, $group);
        }
    }

    /**
     * 线下收银列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getScanOrderList(array $where)
    {
        $where['type'] = 3;
        $where['paid'] = 1;
        [$page, $limit] = $this->getPageValue();
        if ($where['add_time']) {
            [$startTime, $endTime] = explode('-', $where['add_time']);
            if ($startTime || $endTime) {
                $startTime = strtotime($startTime);
                $endTime = strtotime($endTime . ' 23:59:59');
                $where['add_time'] = [$startTime, $endTime];
            }
        }
        if ($where['name']) {
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getUserInfoList(['nickname' => $where['name']], "uid");
            if ($userInfo) $where['uid'] = array_column($userInfo, 'uid');
        }
        $list = $this->dao->getScanOrderList($where, $page, $limit);
        if ($list) {
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getColumn([['uid', 'in', array_unique(array_column($list, 'uid'))]], 'uid,phone,nickname', 'uid');
            foreach ($list as &$v) {
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['pay_time'] = date('Y-m-d H:i:s', $v['pay_time']);
                $v['phone'] = $userInfo[$v['uid']]['phone'] ?? '';
                $v['nickname'] = $userInfo[$v['uid']]['nickname'] ?? '';
                switch ($v['pay_type']) {
                    case "yue" :
                        $v['pay_type'] = "余额";
                        break;
                    case "weixin" :
                        $v['pay_type'] = "微信";
                        break;
                    case "alipay" :
                        $v['pay_type'] = "支付宝";
                        break;
                }
                $v['true_price'] = bcsub($v['money'], $v['pay_price'], 2);
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取会员记录
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMemberRecord(array $where)
    {
        $where['type'] = [0, 1, 2];
        $where['paid'] = 1;
        if ($where['add_time']) {
            $searchTime = explode('-', $where['add_time']);
            $startTime = strtotime($searchTime[0]);
            $endTime = strtotime($searchTime[1]);
            if ($startTime == $endTime) {
                $endTime += 86400;
            }
            $where['add_time'] = [$startTime, $endTime];
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getMemberRecord($where, $page, $limit);
        if ($list) {
            /** @var MemberShipServices $memberShipService */
            $memberShipService = app()->make(MemberShipServices::class);
            $shipInfo = $memberShipService->getApiList([]);
            $shipInfo = array_column($shipInfo, 'title', 'type');
            $shipInfo['owner'] = '自定义';
            foreach ($list as &$v) {
                $v['member_type'] = $v['member_type'] ? $shipInfo[$v['member_type']] : '其他';
                $v['pay_time'] = date('Y-m-d H:i:s', $v['pay_time']);
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['overdue_time'] = date('Y-m-d H:i:s', $v['overdue_time']);
                switch ($v['pay_type']) {
                    case "yue" :
                        $v['pay_type'] = "余额";
                        break;
                    case "weixin" :
                        $v['pay_type'] = "微信";
                        break;
                    case "alipay" :
                        $v['pay_type'] = "支付宝";
                        break;
                    case "admin" :
                        $v['pay_type'] = "后台赠送";
                        break;
                }
                if ($v['type'] == 0) $v['pay_type'] = "免费领取";
                if ($v['type'] == 2) {
                    $v['pay_type'] = "卡密领取";
                    $v['member_type'] = "卡密激活";
                }
                if ($v['type'] == 1 && $v['is_free'] == 1) $v['pay_type'] = "免费领取";
                $v['user']['overdue_time'] = date('Y-m-d', $v['user']['overdue_time']) == "1970-01-01" ? "" : date('Y-m-d H:i:s', $v['user']['overdue_time']);
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

}
