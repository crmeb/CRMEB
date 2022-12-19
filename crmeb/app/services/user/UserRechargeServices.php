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
declare (strict_types=1);

namespace app\services\user;

use app\dao\user\UserRechargeDao;
use app\services\BaseServices;
use app\services\order\StoreOrderCreateServices;
use app\services\pay\RechargeServices;
use app\services\statistic\CapitalFlowServices;
use app\services\system\config\SystemGroupDataServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use crmeb\services\pay\Pay;
use think\facade\Route as Url;

/**
 *
 * Class UserRechargeServices
 * @package app\services\user
 * @method be($map, string $field = '') 查询一条数据是否存在
 * @method getDistinctCount(array $where, $field, ?bool $search = true)
 * @method getTrendData($time, $type, $timeType)
 */
class UserRechargeServices extends BaseServices
{

    /**
     * UserRechargeServices constructor.
     * @param UserRechargeDao $dao
     */
    public function __construct(UserRechargeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取单条数据
     * @param int $id
     * @param array $field
     */
    public function getRecharge(int $id, array $field = [])
    {
        return $this->dao->get($id, $field);
    }

    /**
     * 获取统计数据
     * @param array $where
     * @param string $field
     * @return float
     */
    public function getRechargeSum(array $where, string $field = '')
    {
        $whereData = [];
        if (isset($where['data'])) {
            $whereData['time'] = $where['data'];
        }
        if (isset($where['paid']) && $where['paid'] != '') {
            $whereData['paid'] = $where['paid'];
        }
        if (isset($where['nickname']) && $where['nickname']) {
            $whereData['like'] = $where['nickname'];
        }
        if (isset($where['recharge_type']) && $where['recharge_type']) {
            $whereData['recharge_type'] = $where['recharge_type'];
        }
        return $this->dao->getWhereSumField($whereData, $field);
    }

    /**
     * 获取充值列表
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getRechargeList(array $where, string $field = '*', $is_page = true)
    {
        $whereData = [];
        if (isset($where['data'])) {
            $whereData['time'] = $where['data'];
        }
        if (isset($where['paid']) && $where['paid'] != '') {
            $whereData['paid'] = $where['paid'];
        }
        if (isset($where['nickname']) && $where['nickname']) {
            $whereData['like'] = $where['nickname'];
        }
        [$page, $limit] = $this->getPageValue($is_page);
        $list = $this->dao->getList($whereData, $field, $page, $limit);
        $count = $this->dao->count($whereData);

        foreach ($list as &$item) {
            switch ($item['recharge_type']) {
                case 'routine':
                    $item['_recharge_type'] = '小程序充值';
                    break;
                case 'weixin':
                    $item['_recharge_type'] = '公众号充值';
                    break;
                case 'system':
                    $item['_recharge_type'] = '系统充值';
                    break;
                default:
                    $item['_recharge_type'] = '其他充值';
                    break;
            }
            $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '暂无';
            $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '暂无';
            $item['paid_type'] = $item['paid'] ? '已支付' : '未支付';
            $item['avatar'] = strpos($item['avatar'], 'http') === false ? (sys_config('site_url') . $item['avatar']) : $item['avatar'];
            unset($item['user']);
        }
        return compact('list', 'count');
    }

    /**
     * 获取用户充值数据
     * @return array
     */
    public function user_recharge(array $where)
    {
        $data = [];
        $data['sumPrice'] = $this->getRechargeSum($where, 'price');
        $data['sumRefundPrice'] = $this->getRechargeSum($where, 'refund_price');
        $where['recharge_type'] = 'routine';
        $data['sumRoutinePrice'] = $this->getRechargeSum($where, 'price');
        $where['recharge_type'] = 'weixin';
        $data['sumWeixinPrice'] = $this->getRechargeSum($where, 'price');
        return [
            [
                'name' => '充值总金额',
                'field' => '元',
                'count' => $data['sumPrice'],
                'className' => 'logo-yen',
                'col' => 6,
            ],
            [
                'name' => '充值退款金额',
                'field' => '元',
                'count' => $data['sumRefundPrice'],
                'className' => 'logo-usd',
                'col' => 6,
            ],
            [
                'name' => '小程序充值金额',
                'field' => '元',
                'count' => $data['sumRoutinePrice'],
                'className' => 'logo-bitcoin',
                'col' => 6,
            ],
            [
                'name' => '公众号充值金额',
                'field' => '元',
                'count' => $data['sumWeixinPrice'],
                'className' => 'ios-bicycle',
                'col' => 6,
            ],
        ];
    }

    /**退款表单
     * @param $id
     * @return mixed|void
     */
    public function refund_edit(int $id)
    {
        $UserRecharge = $this->getRecharge($id);
        if (!$UserRecharge) {
            throw new AdminException(100026);
        }
        if ($UserRecharge['paid'] != 1) {
            throw new AdminException(400677);
        }
        if ($UserRecharge['price'] == $UserRecharge['refund_price']) {
            throw new AdminException(400147);
        }
        if ($UserRecharge['recharge_type'] == 'balance') {
            throw new AdminException(400678);
        }
        $f = array();
        $f[] = Form::input('order_id', '退款单号', $UserRecharge->getData('order_id'))->disabled(true);
        $f[] = Form::radio('refund_price', '状态', 1)->options([['label' => '本金(扣赠送余额)', 'value' => 1], ['label' => '仅本金', 'value' => 0]]);
//        $f[] = Form::number('refund_price', '退款金额', (float)$UserRecharge->getData('price'))->precision(2)->min(0)->max($UserRecharge->getData('price'));
        return create_form('编辑', $f, Url::buildUrl('/finance/recharge/' . $id), 'PUT');
    }

    /**
     * 退款操作
     * @param int $id
     * @param $refund_price
     * @return mixed
     */
    public function refund_update(int $id, string $refund_price)
    {
        $UserRecharge = $this->getRecharge($id);
        if (!$UserRecharge) {
            throw new AdminException(100026);
        }
        if ($UserRecharge['price'] == $UserRecharge['refund_price']) {
            throw new AdminException(400147);
        }
        if ($UserRecharge['recharge_type'] == 'balance') {
            throw new AdminException(400678);
        }
        $data['refund_price'] = $UserRecharge['price'];
        $refund_data['pay_price'] = $UserRecharge['price'];
        $refund_data['refund_price'] = $UserRecharge['price'];
        if ($refund_price == 1) {
            $number = bcadd($UserRecharge['price'], $UserRecharge['give_price'], 2);
        } else {
            $number = $UserRecharge['price'];
        }

        try {
            $recharge_type = $UserRecharge['recharge_type'];
            /** @var Pay $pay */
            $pay = app()->make(Pay::class);
            if ($recharge_type == 'weixin') {
                $refund_data['wechat'] = true;
                $pay->refund($UserRecharge['order_id'], $refund_data);
            } else {
                $refund_data['trade_no'] = $UserRecharge['trade_no'];
                $refund_data['order_id'] = $UserRecharge['order_id'];
                /** @var WechatUserServices $wechatUserServices */
                $wechatUserServices = app()->make(WechatUserServices::class);
                $refund_data['open_id'] = $wechatUserServices->uidToOpenid((int)$UserRecharge['uid'],'routine') ?? '';
                $refund_data['pay_new_weixin_open'] = sys_config('pay_new_weixin_open');
                /** @var StoreOrderCreateServices $storeOrderCreateServices  */
                $storeOrderCreateServices = app()->make(StoreOrderCreateServices::class);
                $refund_data['refund_no'] = $storeOrderCreateServices->getNewOrderId('tk');
                $pay->refund($UserRecharge['order_id'], $refund_data);
            }
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        if (!$this->dao->update($id, $data)) {
            throw new AdminException(100007);
        }

        //修改用户余额
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($UserRecharge['uid']);
        if ($userInfo['now_money'] > $number) {
            $now_money = bcsub((string)$userInfo['now_money'], $number, 2);
        } else {
            $number = $userInfo['now_money'];
            $now_money = 0;
        }
        $userServices->update((int)$UserRecharge['uid'], ['now_money' => $now_money], 'uid');

        //写入资金流水
        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        $UserRecharge['nickname'] = $userInfo['nickname'];
        $UserRecharge['phone'] = $userInfo['phone'];
        $capitalFlowServices->setFlow($UserRecharge, 'refund_recharge');

        //保存余额记录
        /** @var UserMoneyServices $userMoneyServices */
        $userMoneyServices = app()->make(UserMoneyServices::class);
        $userMoneyServices->income('user_recharge_refund', $UserRecharge['uid'], $number, $now_money, $id);

        //提醒推送
        event('notice.notice', [['user_type' => strtolower($userInfo['user_type']), 'data' => $data, 'UserRecharge' => $UserRecharge, 'now_money' => $refund_price], 'recharge_order_refund_status']);
        return true;
    }

    /**
     * 删除
     * @param int $id
     * @return bool
     */
    public function delRecharge(int $id)
    {
        $rechargInfo = $this->getRecharge($id);
        if (!$rechargInfo) throw new AdminException(100026);
        if ($rechargInfo->paid) {
            throw new AdminException(400679);
        }
        if ($this->dao->delete($id))
            return true;
        else
            throw new AdminException(100008);
    }

    /**
     * 生成充值订单号
     * @return bool|string
     */
    public function getOrderId()
    {
        return 'wx' . date('YmdHis', time()) . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 导入佣金到余额
     * @param int $uid
     * @param $price
     * @return bool
     */
    public function importNowMoney(int $uid, $price)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(100100);
        }
        /** @var UserBrokerageServices $frozenPrices */
        $frozenPrices = app()->make(UserBrokerageServices::class);
        $broken_commission = $frozenPrices->getUserFrozenPrice($uid);
        $commissionCount = bcsub((string)$user['brokerage_price'], (string)$broken_commission, 2);
        if ($price > $commissionCount) {
            throw new ApiException(400680);
        }
        $edit_data = [];
        $edit_data['now_money'] = bcadd((string)$user['now_money'], (string)$price, 2);
        $edit_data['brokerage_price'] = $user['brokerage_price'] > $price ? bcsub((string)$user['brokerage_price'], (string)$price, 2) : 0;
        if (!$userServices->update($uid, $edit_data, 'uid')) {
            throw new ApiException(100007);
        }

        //写入充值记录
        $rechargeInfo = [
            'uid' => $uid,
            'order_id' => $this->getOrderId(),
            'recharge_type' => 'balance',
            'price' => $price,
            'give_price' => 0,
            'paid' => 1,
            'pay_time' => time(),
            'add_time' => time()
        ];
        if (!$re = $this->dao->save($rechargeInfo)) {
            throw new ApiException(400681);
        }

        //余额记录
        /** @var UserMoneyServices $userMoneyServices */
        $userMoneyServices = app()->make(UserMoneyServices::class);
        $userMoneyServices->income('brokerage_to_nowMoney', $uid, $price, $edit_data['now_money'], $re['id']);

        //写入提现记录
        $extractInfo = [
            'uid' => $uid,
            'real_name' => $user['nickname'],
            'extract_type' => 'balance',
            'extract_price' => $price,
            'balance' => $user['brokerage_price'],
            'add_time' => time(),
            'status' => 1
        ];
        /** @var UserExtractServices $userExtract */
        $userExtract = app()->make(UserExtractServices::class);
        $userExtract->save($extractInfo);

        //佣金提现记录
        /** @var UserBrokerageServices $userBrokerageServices */
        $userBrokerageServices = app()->make(UserBrokerageServices::class);
        $userBrokerageServices->income('brokerage_to_nowMoney', $uid, $price, $edit_data['brokerage_price'], $re['id']);
        return true;
    }

    /**
     * 申请充值
     * @param int $uid
     * @return mixed
     */
    public function recharge(int $uid, $price, $recharId, $type, $from)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(400214);
        }
        switch ((int)$type) {
            case 0: //支付充值余额
                $paid_price = 0;
                if ($recharId) {
                    /** @var SystemGroupDataServices $systemGroupData */
                    $systemGroupData = app()->make(SystemGroupDataServices::class);
                    $data = $systemGroupData->getDateValue($recharId);
                    if ($data === false) {
                        throw new ApiException(400682);
                    } else {
                        $paid_price = $data['give_money'] ?? 0;
                    }
                }
                $recharge_data = [];
                $recharge_data['order_id'] = app()->make(StoreOrderCreateServices::class)->getNewOrderId('cz');
                $recharge_data['uid'] = $uid;
                $recharge_data['price'] = $price;
                $recharge_data['recharge_type'] = $from;
                $recharge_data['paid'] = 0;
                $recharge_data['add_time'] = time();
                $recharge_data['give_price'] = $paid_price;
                $recharge_data['channel_type'] = $user['user_type'];
                if (!$rechargeOrder = $this->dao->save($recharge_data)) {
                    throw new ApiException(400683);
                }
                try {
                    /** @var RechargeServices $recharge */
                    $recharge = app()->make(RechargeServices::class);
                    $order_info = $recharge->recharge($rechargeOrder);
                } catch (\Exception $e) {
                    throw new ApiException($e->getMessage());
                }
                return ['msg' => '', 'type' => $from, 'data' => $order_info];
            case 1: //佣金转入余额
                $this->importNowMoney($uid, $price);
                return ['msg' => '转入余额成功', 'type' => $from, 'data' => []];
            default:
                throw new ApiException(100100);
        }
    }

    /**
     * //TODO用户充值成功后
     * @param $orderId
     */
    public function rechargeSuccess($orderId,array $other = [])
    {
        $order = $this->dao->getOne(['order_id' => $orderId, 'paid' => 0]);
        if (!$order) {
            throw new ApiException(410173);
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo((int)$order['uid']);
        if (!$user) {
            throw new ApiException(410032);
        }
        $price = bcadd((string)$order['price'], (string)$order['give_price'], 2);
        if (!$this->dao->update($order['id'], ['paid' => 1, 'pay_time' => time() ,'trade_no'=> $other['trade_no'] ?? ''], 'id')) {
            throw new ApiException(410286);
        }
        $now_money = bcadd((string)$user['now_money'], (string)$price, 2);
        /** @var UserMoneyServices $userMoneyServices */
        $userMoneyServices = app()->make(UserMoneyServices::class);
        $userMoneyServices->income('user_recharge', $user['uid'], ['number' => $price, 'price' => $order['price'], 'give_price' => $order['give_price']], $now_money, $order['id']);
        if (!$userServices->update((int)$order['uid'], ['now_money' => $now_money], 'uid')) {
            throw new ApiException(410287);
        }

        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        $order['nickname'] = $user['nickname'];
        $order['phone'] = $user['phone'];
        $capitalFlowServices->setFlow($order, 'recharge');

        //提醒推送
        event('notice.notice', [['order' => $order, 'now_money' => $now_money], 'recharge_success']);
        return true;
    }

    /**根据查询用户充值金额
     * @param array $where
     * @return float|int
     */
    public function getRechargeMoneyByWhere(array $where, string $rechargeSumField, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum" :
                return $this->dao->getWhereSumField($where, $rechargeSumField);
            case "group" :
                return $this->dao->getGroupField($where, $rechargeSumField, $group);
        }
    }
}
