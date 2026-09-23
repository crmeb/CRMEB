<?php
declare(strict_types=1);

namespace app\services\user;

use app\dao\user\OfflineReceiptDao;
use crmeb\basic\BaseService;
use app\services\pay\PayServices;
use app\services\pay\PayRecordServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;
use app\jobs\OfflineReceiptJob;
use app\services\user\OfflineReceiptStatusServices;

/**
 * 线下收银服务类
 * 负责线下收银收款单（扫码自填金额收款）的创建、支付、查询等业务逻辑（对应表 eb_offline_receipt）
 * 会员购买订单由 MemberOrderServices 处理（对应表 eb_member_order）
 */
class OfflineReceiptServices extends BaseService
{
    /**
     * @var OfflineReceiptDao
     */
    protected $dao;

    /**
     * 初始化DAO层实例
     * OfflineReceiptServices constructor.
     * @param OfflineReceiptDao $dao
     */
    public function __construct(OfflineReceiptDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加线下收银订单数据
     * 将收款单数据保存到 eb_offline_receipt 表（订单号存 order_no，order_id 为关联ID置0）
     * @param array $data 订单数据
     * @return mixed 返回订单记录
     * @throws ApiException 数据为空时抛出异常
     */
    public function addOfflineReceiptData(array $data)
    {
        if (!$data) throw new ApiException('数据不存在');
        $add = [
            'uid' => $data['uid'],
            'order_id' => $data['order_id'] ?? 0,
            'order_no' => $data['order_no'],
            'type' => $data['type'] ?? 3,
            'trade_type' => $data['trade_type'] ?? 0,
            'total_price' => $data['money'] ?? 0.00,
            'paid_price' => $data['pay_price'] ?? 0.00,
            'paid' => $data['paid'] ?? 0,
            'pay_time' => $data['pay_time'] ?? 0,
            'status' => 0,
            'add_time' => time(),
            'is_del' => 0,
        ];
        return $this->dao->save($add);
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
     * 创建线下收银订单
     * @param int $uid 用户ID
     * @param string $channelType 渠道类型
     * @param mixed $memberType 会员类型（线下收银不使用）
     * @param string $payPrice 实付金额
     * @param string $payType 支付方式
     * @param string $type 订单类型（固定 3=线下收银）
     * @param float $money 原价金额
     * @param int $mcId 会员卡ID（线下收银不使用）
     * @return mixed 返回创建的订单记录
     * @throws ApiException 订单生成失败时抛出异常
     */
    public function createOrder(int $uid, string $channelType, $memberType = false, string $payPrice = '', string $payType = '', $type = '', $money = 0, $mcId = 0)
    {
        /** @var \app\services\order\OrderCenterServices $storeOrderCreateService */
        $storeOrderCreateService = app()->make(\app\services\order\OrderCenterServices::class);
        $orderInfo = [
            'uid' => $uid,
            'order_no' => $storeOrderCreateService->getNewOrderId('hy'),
            'pay_price' => $payPrice,
            'pay_type' => $payType,
            'channel_type' => $channelType,
            'type' => 3,
            'money' => $money ?: $payPrice,
        ];
        $order = $this->addOfflineReceiptData($orderInfo);
        if (!$order) {
            throw new ApiException('订单生成失败');
        }
        //记录订单状态变更
        /** @var OfflineReceiptStatusServices $statusService */
        $statusService = app()->make(OfflineReceiptStatusServices::class);
        $statusService->save([
            'order_id' => $order['id'],
            'change_type' => 'create_offline_scan_order',
            'change_message' => '订单生成',
        ]);
        return $order;
    }

    /**
     * 支付成功处理
     * 处理线下收银订单支付成功后的业务逻辑，包括记录资金流水等
     * @param array $orderInfo 订单信息
     * @param string $paytype 支付方式
     * @param array $other 其他参数
     * @return bool 返回处理结果
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        if (!\crmeb\services\pay\PaymentDispatch::collecting()) {
            return app()->make(PayRecordServices::class)->complete((string)$orderInfo['order_no'], 'offline_scan',
                (string)($other['trade_no'] ?? ''), fn(array $fresh) => $fresh['paid'] ? true
                    : $this->paySuccess(array_replace($orderInfo, $fresh), $paytype, $other));
        }
        /** @var OfflineReceiptStatusServices $statusService */
        $statusService = app()->make(OfflineReceiptStatusServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $type = 'offline_scan';
        $res1 = true;
        //更新支付状态（eb_offline_receipt 无 trade_no/pay_type 列，仅更新支付状态与支付时间）
        $updata['paid'] = 1;
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
        //线下表字段兼容映射：供 OfflineReceiptJob / CapitalFlowServices 使用
        $orderInfo['order_id'] = $orderInfo['order_no'];
        $orderInfo['money'] = $orderInfo['total_price'];
        $orderInfo['pay_price'] = $orderInfo['paid_price'];
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

        $orderInfo['pay_type'] = $paytype;
        //触发订单发货事件
        event('OrderShipping', ['offline_scan', $orderInfo, 3, '', '']);
        if (!$res) throw new \RuntimeException('收银支付业务处理失败');
        return true;
    }

    /**
     * 获取线下收银订单金额统计
     * 根据条件统计订单金额
     * @param array $where 查询条件
     * @param string $sumField 统计字段
     * @param string $selectType 统计类型
     * @param string $group 分组字段
     * @return mixed 返回统计结果
     */
    public function getMoneyByWhere(array $where, string $sumField, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum":
                return $this->dao->getWhereSumField($where, $sumField);
            case "group":
                return $this->dao->getGroupField($where, $sumField, $group);
        }
    }

    /**
     * 获取线下收银订单列表
     * 查询线下付款订单记录，支持按订单号、时间、用户名筛选
     * @param array $where 查询条件
     * @return array 返回分页后的订单列表数据
     */
    public function getScanOrderList(array $where)
    {
        $where['type'] = 3;
        $where['paid'] = 1;
        //订单号筛选（前端传 order_id，实际对应表 order_no 列）
        if (!empty($where['order_id'])) {
            $where['order_no'] = $where['order_id'];
        }
        unset($where['order_id']);
        [$page, $limit] = $this->getPageValue();
        //时间范围筛选
        if ($where['add_time']) {
            [$startTime, $endTime] = explode('-', $where['add_time']);
            if ($startTime || $endTime) {
                $startTime = strtotime($startTime);
                $endTime = strtotime($endTime . ' 23:59:59');
                $where['add_time'] = [$startTime, $endTime];
            }
        }
        //用户名筛选
        if ($where['name']) {
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getUserInfoList(['nickname' => $where['name']], "uid");
            if ($userInfo) $where['uid'] = array_column($userInfo, 'uid');
        }
        $list = $this->dao->getList($where, '*', $page, $limit);
        if ($list) {
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getColumn([['uid', 'in', array_unique(array_column($list, 'uid'))]], 'uid,phone,nickname', 'uid');
            foreach ($list as &$v) {
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['pay_time'] = date('Y-m-d H:i:s', $v['pay_time']);
                $v['phone'] = $userInfo[$v['uid']]['phone'] ?? '';
                $v['nickname'] = $userInfo[$v['uid']]['nickname'] ?? '';
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
                }
                //兼容前端字段：money/pay_price 映射为 total_price/paid_price
                $v['money'] = $v['total_price'];
                $v['pay_price'] = $v['paid_price'];
                $v['true_price'] = bcsub($v['total_price'], $v['paid_price'], 2);
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }
}
