<?php
declare(strict_types=1);

namespace app\services\user;

use app\dao\user\MemberOrderDao;
use crmeb\basic\BaseService;
use app\dao\order\OrderEconomizeDao;
use app\services\order\OrderCenterServices;
use app\services\pay\PayServices;
use app\services\pay\PayRecordServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\member\MemberShipServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserServices;
use app\services\user\member\MemberCardServices;
use crmeb\exceptions\ApiException;
use app\jobs\OfflineReceiptJob;
use app\services\user\MemberOrderStatusServices;

/**
 * 会员订单服务类
 * 负责会员购买订单（含免费领取、卡密领取）的创建、支付、查询等业务逻辑
 * 对应表 eb_member_order，线下收银订单由 OfflineReceiptServices 处理
 */
class MemberOrderServices extends BaseService
{
    /**
     * @var MemberOrderDao
     */
    protected $dao;

    /**
     * 初始化DAO层实例
     * MemberOrderServices constructor.
     * @param MemberOrderDao $dao
     */
    public function __construct(MemberOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加会员订单数据
     * 将会员订单数据保存到 eb_member_order 表
     * @param array $data 订单数据
     * @return mixed 返回订单记录
     * @throws ApiException 数据为空时抛出异常
     */
    public function addMemberOrderData(array $data)
    {
        if (!$data) throw new ApiException('数据不存在');
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
     * 检查用户是否可以领取免费会员
     * 查询用户是否已领取过免费会员，并返回免费会员配置信息
     * @param int $uid 用户ID
     * @return array 返回免费会员配置及用户信息
     */
    public function isCanGetFree(int $uid)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        /** @var OrderEconomizeDao $economizeDao */
        $economizeDao = app()->make(OrderEconomizeDao::class);
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
        // 累计节省金额（邮费/会员价/线下/优惠券优惠求和）
        $economizeMoney = 0.00;
        $economizeList = $economizeDao->getList(['uid' => $uid]);
        if ($economizeList) {
            foreach ($economizeList as $economize) {
                $economizeMoney += $economize['postage_price'];
                $economizeMoney += $economize['member_price'];
                $economizeMoney += $economize['offline_price'];
                $economizeMoney += $economize['coupon_price'];
            }
        }
        $userInfo['economize_money'] = sprintf("%.2f", $economizeMoney);
        $userInfo['shop_name'] = sys_config('site_name');
        $freeConfig['user_info'] = $userInfo;
        return $freeConfig;
    }

    /**
     * 时间转换工具方法
     * 将时间关键词转换为具体的时间范围
     * @param mixed $timeKey 时间关键词或时间范围数组
     * @param bool $isNum 是否为数字天数
     * @return array 返回时间范围数组
     */
    public function TimeConvert($timeKey, $isNum = false)
    {
        switch ($timeKey) {
            case "today":
                $data['start_time'] = date('Y-m-d 00:00:00', time());
                $data['end_time'] = date('Y-m-d 23:59:59', time());
                $data['days'] = 1;
                break;
            case "yestoday":
                $data['start_time'] = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $data['end_time'] = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $data['days'] = 1;
                break;
            case "last_month":
                $data['start_time'] = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $data['end_time'] = date('Y-m-t 23:59:59', strtotime('-1 month'));
                $data['days'] = 30;
                break;
            case "month":
                $data['start_time'] = $month_start_time = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
                $data['end_time'] = date('Y-m-d 23:59:59', strtotime("$month_start_time +1 month -1 day"));
                $data['days'] = 30;
                break;
            case "year":
                $data['start_time'] = date('Y-01-01 00:00:00', time());
                $data['end_time'] = date('Y-12-t 23:59:59', time());
                $data['days'] = 365;
                break;
            case "last_year":
                $data['start_time'] = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $data['end_time'] = date('Y-12-t 23:59:59', strtotime('-1 year'));
                $data['days'] = 365;
                break;
            case 30:
            case 15:
            case 7:
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
     * 创建会员订单
     * 创建会员购买/免费领取/卡密领取订单
     * @param int $uid 用户ID
     * @param string $channelType 渠道类型
     * @param mixed $memberType 会员类型
     * @param string $payPrice 支付金额
     * @param string $payType 支付方式
     * @param string $type 订单类型
     * @param float $money 金额
     * @param int $mcId 会员卡ID
     * @return mixed 返回创建的订单记录
     * @throws ApiException 订单生成失败时抛出异常
     */
    public function createOrder(int $uid, string $channelType, $memberType = false, string $payPrice = '', string $payType = '', $type = '', $money = 0, $mcId = 0)
    {
        /** @var OrderCenterServices $orderCenterServices */
        $orderCenterServices = app()->make(OrderCenterServices::class);
        $orderInfo = [
            'uid' => $uid,
            // 会员订单号统一走订单中台生成
            'order_id' => $orderCenterServices->getNewOrderId('hy'),
            'pay_price' => $payPrice,
            'pay_type' => $payType,
            'channel_type' => $channelType,
            'member_code' => "",
        ];
        //会员购买订单
        if (!$memberType) throw new ApiException('会员类型不存在');
        list($memberPrice, $isFree, $isPermanent, $overdueTime, $type, $newMemberRight) = $this->checkPayMemberType($memberType, $payPrice, $type, $uid, $mcId);
        $orderInfo['member_price'] = $memberPrice;
        $orderInfo['money'] = $memberPrice;
        $orderInfo['vip_day'] = $newMemberRight[$mcId]['vip_day'];
        $orderInfo['member_type'] = $memberType;
        $orderInfo['overdue_time'] = $overdueTime;
        $orderInfo['is_permanent'] = $isPermanent;
        $orderInfo['is_free'] = $isFree;
        $orderInfo['type'] = $type;
        $memberOrder = $this->addMemberOrderData($orderInfo);
        if (!$memberOrder) {
            throw new ApiException('订单生成失败');
        }
        //记录订单状态变更
        /** @var MemberOrderStatusServices $statusService */
        $statusService = app()->make(MemberOrderStatusServices::class);
        $statusService->save([
            'order_id' => $memberOrder['id'],
            'change_type' => "create_member_order",
            'change_message' => '订单生成',
        ]);
        return $memberOrder;
    }

    /**
     * 零元支付
     * 处理免费会员领取的支付逻辑
     * @param array $orderInfo 订单信息
     * @return mixed 返回支付结果
     * @throws ApiException 订单已支付或支付失败时抛出异常
     */
    public function zeroYuanPayment($orderInfo)
    {
        if ($orderInfo['paid']) {
            throw new ApiException('订单已支付');
        }
        if ($orderInfo['member_type'] != 'free') {
            throw new ApiException('支付失败');
        }
        $res = $this->paySuccess($orderInfo, 'yue');
        return $res;
    }

    /**
     * 支付成功处理
     * 处理会员订单支付成功后的业务逻辑，包括更新会员过期时间、记录资金流水等
     * @param array $orderInfo 订单信息
     * @param string $paytype 支付方式
     * @param array $other 其他参数
     * @return bool 返回处理结果
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        if (!\crmeb\services\pay\PaymentDispatch::collecting()) {
            return app()->make(PayRecordServices::class)->complete((string)$orderInfo['order_id'], 'member',
                (string)($other['trade_no'] ?? ''), fn(array $fresh) => $fresh['paid'] ? true
                    : $this->paySuccess(array_replace($orderInfo, $fresh), $paytype, $other));
        }
        /** @var MemberOrderStatusServices $statusService */
        $statusService = app()->make(MemberOrderStatusServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $type = 'pay_member';
        $res1 = true;
        //根据订单类型执行不同的支付成功逻辑
        switch ($orderInfo['type']) {
            case 0:
            case 1:
            case 2:
                $type = "pay_member";
                $res1 = $userServices->setMemberOverdueTime($orderInfo['vip_day'], $orderInfo['uid'], 1, $orderInfo['member_type']);
                break;
        }
        //支付宝支付记录交易号
        if (isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }
        $updata['paid'] = 1;
        $updata['pay_type'] = $paytype;
        $updata['pay_time'] = time();
        $orderInfo['pay_time'] = $updata['pay_time'];
        $res2 = $this->dao->update($orderInfo['id'], $updata);
        //记录订单状态变更
        $res3 = $statusService->save([
            'order_id' => $orderInfo['id'],
            'change_type' => 'pay_success',
            'change_message' => '用户付款成功',
        ]);
        $res4 = true;
        // 余额扣款由 YuePayServices 在同一支付事务中完成，此处不能重复扣款。
        $orderInfo['pay_type'] = $paytype;
        //派发异步任务
        OfflineReceiptJob::dispatch([$orderInfo]);
        $orderInfo['is_channel'] = 2;
        $orderInfo['total_num'] = 1;

        //非余额支付记录资金流水
        if ($orderInfo['pay_type'] != 'yue') {
            /** @var CapitalFlowServices $capitalFlowServices */
            $capitalFlowServices = app()->make(CapitalFlowServices::class);
            $userInfo = $userServices->get($orderInfo['uid']);
            $orderInfo['nickname'] = $userInfo['nickname'];
            $orderInfo['phone'] = $userInfo['phone'];
            $capitalFlowServices->setFlow($orderInfo, $type);
        }
        $res = $res1 && $res2 && $res3 && $res4;
        //分销佣金处理
        if (sys_config('member_brokerage', 0) == 1 && sys_config('brokerage_func_status', 0) == 1) {
            $spread_one = sys_config('is_self_brokerage') ? $orderInfo['uid'] : $userServices->getSpreadUid($orderInfo['uid']);
            $spread_two = sys_config('brokerage_level', 2) == 2 ? $userServices->getSpreadUid($spread_one, [], false) : 0;
            $spread_one_price = bcmul((string)$orderInfo['pay_price'], (string)bcdiv((string)sys_config('store_brokerage_ratio', 0), '100', 4), 2);
            $spread_two_price = bcmul((string)$orderInfo['pay_price'], (string)bcdiv((string)sys_config('store_brokerage_two', 0), '100', 4), 2);
            if ($spread_one && $spread_one_price > 0 && $userServices->checkUserPromoter($spread_one)) $this->memberBrokerage($spread_one, $spread_one_price, sys_config('is_self_brokerage') ? 'get_self_member_brokerage' : 'get_member_brokerage', $orderInfo);
            if ($spread_two && $spread_two_price > 0 && $userServices->checkUserPromoter($spread_two)) $this->memberBrokerage($spread_two, $spread_two_price, 'get_two_member_brokerage', $orderInfo);
        }

        $orderInfo['pay_type'] = $paytype;
        //触发订单发货事件
        event('OrderShipping', ['member', $orderInfo, 3, '', '']);
        if (!$res) throw new \RuntimeException('会员支付业务处理失败');
        return true;
    }

    /**
     * 会员分销佣金处理
     * 处理会员购买产生的分销佣金
     * @param int $uid 分销员ID
     * @param float $price 佣金金额
     * @param string $type 佣金类型
     * @param array $orderInfo 订单信息
     */
    public function memberBrokerage($uid, $price, $type, $orderInfo)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($uid);
        $broken_time = intval(sys_config('extract_time'));
        $frozen_time = time() + $broken_time * 86400;
        /** @var UserBrokerageServices $userBrokerageServices */
        $userBrokerageServices = app()->make(UserBrokerageServices::class);
        $userBrokerageServices->brokerageIncome($type, $uid, [
            'nickname' => $userInfo['nickname'],
            'pay_price' => floatval($orderInfo['pay_price']),
            'number' => floatval($price),
            'frozen_time' => $frozen_time
        ], $orderInfo['id']);
    }

    /**
     * 验证支付会员类型
     * 根据会员类型验证支付参数，计算会员价格、过期时间等
     * @param string $memberType 会员类型
     * @param string $payPrice 支付金额
     * @param string $type 订单类型
     * @param int $uid 用户ID
     * @param int $mcId 会员卡ID
     * @return array 返回会员价格、是否免费、是否永久、过期时间、订单类型、会员权益配置
     * @throws ApiException 参数验证失败时抛出异常
     */
    public function checkPayMemberType(string $memberType, string $payPrice, string $type, $uid, $mcId)
    {
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($uid);
        if ($userInfo['is_money_level'] > 0 && $userInfo['is_ever_level'] > 0) throw new ApiException('您已是永久会员无需再购买');
        $newMemberRight = $memberCardService->getMemberTypeValue();
        if (!array_key_exists($mcId, $newMemberRight)) throw new ApiException('该会员卡暂时无法购买');
        $price = $newMemberRight[$mcId]['pre_price'];
        if ($payPrice != $price || ($memberType != 'free' && $payPrice <= 0)) throw new ApiException('参数错误');
        if ($memberType == 'free' && $newMemberRight[$mcId]['vip_day'] <= 0) throw new ApiException('参数错误');
        //根据会员类型计算价格和过期时间
        switch ($memberType) {
            case "free":
                $isCanGetFree = $this->isCanGetFree($uid);
                if ($isCanGetFree['is_record'] == 1) throw new ApiException('您已经领取过免费会员');
                $memberPrice = 0.00;
                $isFree = 1;
                $isPermanent = 0;
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
                $overdueTime = 0;
                break;
            default:
                throw new ApiException('此类型会员卡暂未开售');
        }
        return [$memberPrice, $isFree, $isPermanent, $overdueTime, $type, $newMemberRight];
    }

    /**
     * 获取会员订单金额统计
     * 根据条件统计订单金额
     * @param array $where 查询条件
     * @param string $sumField 统计字段
     * @param string $selectType 统计类型
     * @param string $group 分组字段
     * @return mixed 返回统计结果
     */
    public function getMemberMoneyByWhere(array $where, string $sumField, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum":
                return $this->dao->getWhereSumField($where, $sumField);
            case "group":
                return $this->dao->getGroupField($where, $sumField, $group);
        }
    }

    /**
     * 获取会员记录列表
     * 查询用户购买或领取会员卡的记录，支持按用户名、时间、会员类型筛选
     * @param array $where 查询条件
     * @return array 返回分页后的会员记录列表数据
     */
    public function getMemberRecord(array $where)
    {
        $where['type'] = [0, 1, 2];
        $where['paid'] = 1;
        //时间范围筛选
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
        $list = $this->dao->getList($where, '*', $page, $limit);
        if ($list) {
            /** @var MemberShipServices $memberShipService */
            $memberShipService = app()->make(MemberShipServices::class);
            $shipInfo = $memberShipService->getApiList([]);
            $shipInfo = array_column($shipInfo, 'title', 'type');
            $shipInfo['owner'] = '自定义';
            foreach ($list as &$v) {
                $v['member_type'] = $v['member_type'] ? $shipInfo[$v['member_type']] ?? '其他' : '其他';
                $v['pay_time'] = date('Y-m-d H:i:s', $v['pay_time']);
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['overdue_time'] = date('Y-m-d H:i:s', $v['overdue_time']);
                //支付方式转换
                switch ($v['pay_type']) {
                    case "yue":
                        $v['pay_type'] = "余额";
                        break;
                    case "weixin":
                        $v['pay_type'] = "微信";
                        break;
                    case "alipay":
                        $v['pay_type'] = "支付宝";
                        break;
                    case 'allinpay':
                        $v['pay_type'] = "通联支付";
                        break;
                    case "admin":
                        $v['pay_type'] = "后台赠送";
                        break;
                }
                //订单类型特殊处理
                if ($v['type'] == 0) $v['pay_type'] = "免费领取";
                if ($v['type'] == 2) {
                    $v['pay_type'] = "卡密领取";
                    $v['member_type'] = "卡密激活";
                }
                if ($v['type'] == 1 && $v['is_free'] == 1) $v['pay_type'] = "免费领取";
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }
}
