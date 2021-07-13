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

namespace app\services\order;


use app\dao\order\OtherOrderDao;
use app\services\BaseServices;
use app\services\pay\PayServices;
use app\services\statistic\TradeStatisticServices;
use app\services\user\MemberShipServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use app\services\user\MemberCardServices;
use crmeb\services\GroupDataService;
use app\jobs\OtherOrderJob;
use think\App;
use think\exception\ValidateException;

/**
 * Class OtherOrderServices
 * @package app\services\order
 * @method getDistinctCount(array $where, $field, ?bool $search = true)
 * @method getPayUserCount(int $time, string $channel_type)
 * @method getTrendData($time, $type, $timeType)
 */
class OtherOrderServices extends BaseServices
{

    /**初始化，获得dao层句柄
     * OtherOrderServices constructor.
     * @param OtherOrderDao $dao
     */
    public function __construct(OtherOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /** 生成会员购买订单数据
     * @param array $data
     * @return mixed
     */
    public function addOtherOrderData(array $data)
    {
        if (!$data) throw new ValidateException('数据不能为空');
        $add = [
            'uid' => $data['uid'],
            'type' => isset($data['type']) ? $data['type'] : 1,
            'order_id' => $data['order_id'],
            'channel_type' => $data['channel_type'],
            'pay_type' => isset($data['pay_type']) ? $data['pay_type'] : 0,
            'member_type' => isset($data['member_type']) ? $data['member_type'] : 0,
            'member_price' => isset($data['member_price']) ? $data['member_price'] : 0.00,
            'pay_price' => isset($data['pay_price']) ? $data['pay_price'] : 0.00,
            'code' => isset($data['member_code']) ? $data['member_code'] : "",
            'vip_day' => isset($data['vip_day']) ? $data['vip_day'] : 0,
            'is_permanent' => isset($data['is_permanent']) ? $data['is_permanent'] : 0,
            'is_free' => isset($data['is_free']) ? $data['is_free'] : 0,
            'overdue_time' => isset($data['overdue_time']) ? $data['overdue_time'] : 0,
            'status' => 0,
            'paid' => isset($data['paid']) ? $data['paid'] : 0,
            'pay_time' => isset($data['pay_time']) ? $data['pay_time'] : 0,
            'money' => isset($data['money']) ? $data['money'] : 0,
            'add_time' => time(),
        ];
        return $this->dao->save($add);
    }


    /** 查询会员卡订单数据
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
    public function createOrder(int $uid, string $channelType, $memberType = false, string $payPrice, string $payType, $type, $money)
    {
        /** @var StoreOrderCreateServices $storeOrderCreateService */
        $storeOrderCreateService = app()->make(StoreOrderCreateServices::class);
        $orderInfo = [
            'uid' => $uid,
            'order_id' => $storeOrderCreateService->getNewOrderId(),
            'pay_price' => $payPrice,
            'pay_type' => $payType,
            'channel_type' => $channelType,
            'member_code' => "",
        ];
        $orderInfo['type'] = $type;
        $orderInfo['member_code'] = "";
        $changeType = "create_offline_scan_order";
        $orderInfo['money'] = $money ? $money : $payPrice;
        $memberOrder = $this->addOtherOrderData($orderInfo);
        if (!$memberOrder) {
            throw new ValidateException('订单生成失败!');
        }
        // CacheService::redisHandler()->delete('user_order_' . $uid . $key);
        /** @var OtherOrderStatusServices $statusService */
        $statusService = app()->make(OtherOrderStatusServices::class);
        $statusService->save([
            'oid' => $memberOrder['id'],
            'change_type' => $changeType,
            'change_message' => '订单生成',
            'change_time' => time(),
            'shop_type' => $type,
        ]);
        //$this->pushJob($order['id'], $combinationId, $seckillId, $bargainId);
        return $memberOrder;
    }

    /** 免费卡领取支付
     * @param $orderInfo
     * @return bool
     */
    public function zeroYuanPayment($orderInfo)
    {
        if ($orderInfo['paid']) {
            throw new ValidateException('该订单已支付!');
        }
        if ($orderInfo['member_type'] != 'free') {
            throw new ValidateException('支付失败!');
        }
        $res = $this->paySuccess($orderInfo, 'yue');//余额支付成功
        return $res;

    }

    /**  会员卡支付成功
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
        switch ($orderInfo['type']) {
            case 0 :
            case 1:
            case 2 :
                $type = "pay_member";
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
        $res = $res1 && $res2 && $res3 && $res4;
        return false !== $res;
    }

    /** 修改
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function update(array $where, array $data)
    {
        return $this->dao->update($where, $data);
    }


    /**线下收银列表
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
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        if ($list) {
            foreach ($list as &$v) {
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['pay_time'] = date('Y-m-d H:i:s', $v['pay_time']);
                $userInfo = $userService->getUserInfo($v['uid']);
                $v['phone'] = $userInfo['phone'];
                $v['nickname'] = $userInfo['nickname'];
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

}
