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
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserRechargeDao;
use app\services\pay\RechargeServices;
use app\services\system\config\SystemGroupDataServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\AdminException;
use crmeb\jobs\RoutineTemplateJob;
use crmeb\jobs\WechatTemplateJob as TemplateJob;
use crmeb\utils\Queue;
use crmeb\services\{
    FormBuilder as Form, MiniProgramService, WechatService
};
use think\exception\ValidateException;
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
                default:
                    $item['_recharge_type'] = '其他充值';
                    break;
            }
            $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '暂无';
            $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '暂无';
            $item['paid_type'] = $item['paid'] ? '已支付' : '未支付';
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
            throw new AdminException('数据不存在!');
        }
        if ($UserRecharge['paid'] != 1) {
            throw new AdminException('订单未支付');
        }
        if ($UserRecharge['price'] == $UserRecharge['refund_price']) {
            throw new AdminException('已退完支付金额!不能再退款了');
        }
        if ($UserRecharge['recharge_type'] == 'balance') {
            throw new AdminException('佣金转入余额，不能退款');
        }
        $f = array();
        $f[] = Form::input('order_id', '退款单号', $UserRecharge->getData('order_id'))->disabled(true);
        $f[] = Form::radio('refund_price', '状态', 1)->options([['label' => '本金+赠送', 'value' => 1], ['label' => '仅本金', 'value' => 0]]);
//        $f[] = Form::number('refund_price', '退款金额', (float)$UserRecharge->getData('price'))->precision(2)->min(0)->max($UserRecharge->getData('price'));
        return create_form('编辑', $f, Url::buildUrl('/finance/recharge/' . $id), 'PUT');
    }

    /**
     * 退款操作
     * @param int $id
     * @param $refund_price
     * @return mixed
     */
    public function refund_update(int $id, $refund_price)
    {
        $UserRecharge = $this->getRecharge($id);
        if (!$UserRecharge) {
            throw new AdminException('数据不存在!');
        }
        if ($UserRecharge['price'] == $UserRecharge['refund_price']) {
            throw new AdminException('已退完支付金额!不能再退款了');
        }
        if ($UserRecharge['recharge_type'] == 'balance') {
            throw new AdminException('佣金转入余额，不能退款');
        }
//        $data['refund_price'] = bcadd($refund_price, $UserRecharge['refund_price'], 2);
        $data['refund_price'] = $UserRecharge['price'];
//        $bj = bccomp((string)$UserRecharge['price'], (string)$data['refund_price'], 2);
//        if ($bj < 0) {
//            throw new AdminException('退款金额大于支付金额，请修改退款金额');
//        }
        $refund_data['pay_price'] = $UserRecharge['price'];
        $refund_data['refund_price'] = $UserRecharge['price'];
//        $refund_data['refund_account']='REFUND_SOURCE_RECHARGE_FUNDS';
        if ($refund_price == 1) {
            $number = bcadd($UserRecharge['price'], $UserRecharge['give_price'], 2);
        } else {
            $number = $UserRecharge['price'];
        }

        try {
            $recharge_type = $UserRecharge['recharge_type'];
            if ($recharge_type == 'weixin') {
                WechatService::payOrderRefund($UserRecharge['order_id'], $refund_data);
            } else {
                MiniProgramService::payOrderRefund($UserRecharge['order_id'], $refund_data);
            }
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        if (!$this->dao->update($id, $data)) {
            throw new AdminException('修改提现数据失败');
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($UserRecharge['uid']);
        $userServices->cutNowMoney($UserRecharge['uid'], $userInfo['now_money'], $number);
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        switch (strtolower($UserRecharge['recharge_type'])) {
            case 'weixin':
                $openid = $wechatServices->uidToOpenid($UserRecharge['uid'], 'wechat');
                Queue::instance()->do('sendRechargeRefundStatus')->job(TemplateJob::class)->data($openid, $data, $UserRecharge)->push();
                break;
            case 'routine':
//                $openid = $wechatServices->uidToOpenid($UserRecharge['uid'], 'routine');
//                Queue::instance()->do('sendOrderRefundSuccess')->job(RoutineTemplateJob::class)->data($openid, $UserRecharge, $refund_price)->push();
                break;
        }
        $billData = ['title' => '系统退款', 'number' => $number, 'link_id' => $id, 'balance' => $userInfo['now_money'], 'mark' => '退款给用户' . $UserRecharge['price'] . '元'];
        /** @var UserBillServices $userBillService */
        $userBillService = app()->make(UserBillServices::class);
        $userBillService->expendNowMoney($UserRecharge['uid'], 'user_recharge_refund', $billData);
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
        if (!$rechargInfo) throw new AdminException('订单未找到');
        if ($rechargInfo->paid) {
            throw new AdminException('已支付的订单记录无法删除');
        }
        if ($this->dao->delete($id))
            return true;
        else
            throw new AdminException('删除失败');
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
            throw new ValidateException('数据不存在');
        }
        /** @var UserBrokerageFrozenServices $frozenPrices */
        $frozenPrices = app()->make(UserBrokerageFrozenServices::class);
        $broken_commission = array_bc_sum($frozenPrices->getUserFrozenPrice($uid));
        $commissionCount = bcsub($user['brokerage_price'], $broken_commission, 2);
        if ($price > $commissionCount) {
            throw new ValidateException('转入金额不能大于可提现佣金！');
        }
        $edit_data = [];
        $edit_data['now_money'] = bcadd((string)$user['now_money'], (string)$price, 2);
        $edit_data['brokerage_price'] = $user['brokerage_price'] > $price ? bcsub((string)$user['brokerage_price'], (string)$price, 2) : 0;
        if (!$userServices->update($uid, $edit_data, 'uid')) {
            throw new ValidateException('修改用户信息失败');
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
            throw new ValidateException('写入余额充值失败');
        }
        $bill_data = ['title' => '用户佣金转入余额', 'link_id' => $re['id'], 'number' => $price, 'balance' => $user['now_money'], 'mark' => '成功转入余额' . floatval($price) . '元'];
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        //余额充值
        $userBill->incomeNowMoney($uid, 'recharge', $bill_data);

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
        if (!$re = $userExtract->save($extractInfo)) {
            throw new ValidateException('写入佣金提现失败');
        }
        //佣金提现
        $userBill->income('brokerage_to_nowMoney', $uid, $price, $user['brokerage_price'], $re['id']);
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
            throw new ValidateException('数据不存在');
        }
        switch ((int)$type) {
            case 0: //支付充值余额
                $paid_price = 0;
                if ($recharId) {
                    /** @var SystemGroupDataServices $systemGroupData */
                    $systemGroupData = app()->make(SystemGroupDataServices::class);
                    $data = $systemGroupData->getDateValue($recharId);
                    if ($data === false) {
                        return app('json')->fail('您选择的充值方式已下架!');
                    } else {
                        $paid_price = $data['give_money'] ?? 0;
                    }
                }
                $recharge_data = [];
                $recharge_data['order_id'] = $this->getOrderId();
                $recharge_data['uid'] = $uid;
                $recharge_data['price'] = $price;
                $recharge_data['recharge_type'] = $from;
                $recharge_data['paid'] = 0;
                $recharge_data['add_time'] = time();
                $recharge_data['give_price'] = $paid_price;
                $recharge_data['channel_type'] = $user['user_type'];
                if (!$rechargeOrder = $this->dao->save($recharge_data)) {
                    throw new ValidateException('充值订单生成失败');
                }
                try {
                    /** @var RechargeServices $recharge */
                    $recharge = app()->make(RechargeServices::class);
                    $order_info = $recharge->recharge((int)$rechargeOrder->id);
                } catch (\Exception $e) {
                    throw new ValidateException($e->getMessage());
                }
                return ['msg' => '', 'type' => $from, 'data' => $order_info];
                break;
            case 1: //佣金转入余额
                $this->importNowMoney($uid, $price);
                return ['msg' => '转入余额成功', 'type' => $from, 'data' => []];
                break;
            default:
                throw new ValidateException('缺少参数');
                break;
        }
    }

    /**
     * //TODO用户充值成功后
     * @param $orderId
     */
    public function rechargeSuccess($orderId)
    {
        $order = $this->dao->getOne(['order_id' => $orderId, 'paid' => 0]);
        if (!$order) {
            throw new ValidateException('订单失效或者不存在');
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo((int)$order['uid']);
        if (!$user) {
            throw new ValidateException('数据不存在');
        }
        $price = bcadd((string)$order['price'], (string)$order['give_price'], 2);
        if (!$this->dao->update($order['id'], ['paid' => 1, 'pay_time' => time()], 'id')) {
            throw new ValidateException('修改订单失败');
        }
        $mark = '成功充值余额' . floatval($order['price']) . '元' . ($order['give_price'] ? ',赠送' . $order['give_price'] . '元' : '');
        $bill_data = ['title' => '用户余额充值', 'number' => $price, 'link_id' => $order['id'], 'balance' => $user['now_money'], 'mark' => $mark];
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $userBill->incomeNowMoney($order['uid'], 'recharge', $bill_data);
        $now_money = bcadd((string)$user['now_money'], (string)$price, 2);
        if (!$userServices->update((int)$order['uid'], ['now_money' => $now_money], 'uid')) {
            throw new ValidateException('修改用户信息失败');
        }
        $wechatServices = app()->make(WechatUserServices::class);
        switch (strtolower($order['recharge_type'])) {
            case 'weixin':

                break;
            case 'routine':
                $openid = $wechatServices->uidToOpenid($order['uid'], 'routine');
                Queue::instance()->do('sendRechargeSuccess')->job(RoutineTemplateJob::class)->data($openid, $order, $now_money)->push();
                break;
        }
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
