<?php
declare(strict_types=1);

namespace app\services\user;

use app\dao\user\UserRechargeDao;
use crmeb\basic\BaseService;
use app\services\pay\PayServices;
use app\services\pay\PayRecordServices;
use app\services\pay\RechargeServices;
use app\services\statistic\CapitalFlowServices;
use app\services\system\config\SystemGroupDataServices;

use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\utils\FormBuilder as Form;
use crmeb\utils\SnowflakeIdGenerator;
use think\facade\Route as Url;

class UserRechargeServices extends BaseService
{
    protected $dao;

    public function __construct(UserRechargeDao $dao)
    {
        $this->dao = $dao;
    }

    public function getRecharge(int $id, array $field = [])
    {
        return $this->dao->get($id, $field);
    }

    public function getRechargeSum(array $where, string $field = '')
    {
        $whereData = [];
        if (isset($where['data'])) {
            $whereData['time'] = $where['data'];
        }
        if (isset($where['paid']) && $where['paid'] !== '' && $where['paid'] !== null) {
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

    public function getRechargeList(array $where, string $field = '*', $is_page = true)
    {
        $whereData = [];
        if (isset($where['data'])) {
            $whereData['time'] = $where['data'];
        }
        if (isset($where['paid']) && $where['paid'] !== '' && $where['paid'] !== null) {
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
                case PayServices::WEIXIN_PAY:
                    $item['_recharge_type'] = '微信充值';
                    break;
                case 'system':
                    $item['_recharge_type'] = '系统充值';
                    break;
                case PayServices::ALIAPY_PAY:
                    $item['_recharge_type'] = '支付宝充值';
                    break;
                default:
                    $item['_recharge_type'] = '其他充值';
                    break;
            }
            $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '暂无';
            $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '暂无';
            $item['paid_type'] = $item['paid'] ? '已支付' : '未支付';
            $item['avatar'] = strpos($item['avatar'] ?? '', 'http') === false ? (sys_config('site_url') . $item['avatar']) : $item['avatar'];
            unset($item['user']);
        }
        return compact('list', 'count');
    }

    public function user_recharge(array $where)
    {
        $data = [];
        $data['sumPrice'] = $this->getRechargeSum($where, 'price');
        $data['sumRefundPrice'] = $this->getRechargeSum($where, 'refund_price');
        $where['recharge_type'] = 'alipay';
        $data['sumAlipayPrice'] = $this->getRechargeSum($where, 'price');
        $where['recharge_type'] = 'weixin';
        $data['sumWeixinPrice'] = $this->getRechargeSum($where, 'price');
        return [
            [
                'name' => '充值总金额',
                'field' => '元',
                'count' => $data['sumPrice'],
                'className' => 'iconjiaoyijine',
                'col' => 6,
            ],
            [
                'name' => '充值退款金额',
                'field' => '元',
                'count' => $data['sumRefundPrice'],
                'className' => 'iconshangpintuikuanjine',
                'col' => 6,
            ],
            [
                'name' => '支付宝充值金额',
                'field' => '元',
                'count' => $data['sumAlipayPrice'],
                'className' => 'iconzhifubao',
                'col' => 6,
            ],
            [
                'name' => '微信充值金额',
                'field' => '元',
                'count' => $data['sumWeixinPrice'],
                'className' => 'iconweixinzhifu',
                'col' => 6,
            ],
        ];
    }

    public function refund_edit(int $id)
    {
        $UserRecharge = $this->getRecharge($id);
        if (!$UserRecharge) {
            throw new AdminException('数据不存在');
        }
        if ($UserRecharge['paid'] != 1) {
            throw new AdminException('订单未支付');
        }
        if ($UserRecharge['price'] == $UserRecharge['refund_price']) {
            throw new AdminException('已退完支付金额，不能再退款了');
        }
        if ($UserRecharge['recharge_type'] == 'balance') {
            throw new AdminException('佣金转入余额，不能退款');
        }
        $f = array();
        $f[] = Form::input('order_id', '退款单号', $UserRecharge->getData('order_id'))->disabled(true);
        $f[] = Form::radio('refund_price', '状态', 1)->options([['label' => '本金(扣赠送余额)', 'value' => 1], ['label' => '仅本金', 'value' => 0]]);
        return create_form('编辑', $f, Url::buildUrl('/finance/recharge/' . $id), 'PUT');
    }

    /**
     * 提交充值原路退款，受理与到账分离，失败可恢复。
     * @param int $id 充值记录ID
     * @param string $refund_price 扣赠送余额模式：1 扣赠送，0 仅本金
     * @return bool
     */
    public function refund_update(int $id, string $refund_price)
    {
        if (!in_array($refund_price, ['0', '1'], true)) throw new AdminException('退款模式不正确');
        try {
            return app()->make(PayRecordServices::class)->refundRecharge($id, $refund_price === '1');
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage(), [], 0, $e);
        }
    }

    public function delRecharge(int $id)
    {
        $rechargInfo = $this->getRecharge($id);
        if (!$rechargInfo) throw new AdminException('数据不存在');
        if ($rechargInfo->paid) {
            throw new AdminException('已支付的订单记录无法删除');
        }
        if ($this->dao->delete($id))
            return true;
        else
            throw new AdminException('删除失败');
    }

    public function getOrderId()
    {
        return 'wx' . date('YmdHis', time()) . substr(implode(NULL, array_map('ord', str_split(substr(uniqid('', true), 7, 13), 1))), 0, 8);
    }

    public function recharge(int $uid, $price, $recharId, $type, $from, bool $renten = false)
    {
        /** @var \app\services\user\UserServices $userServices */
        $userServices = app()->make(\app\services\user\UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException('用户不存在');
        }
        switch ((int)$type) {
            case 0:
                $paid_price = 0;
                if ($recharId) {
                    /** @var SystemGroupDataServices $systemGroupData */
                    $systemGroupData = app()->make(SystemGroupDataServices::class);
                    $data = null;
                    foreach ($systemGroupData->getConfigNameValue('user_recharge_quota') as $quota) {
                        if ((int)$quota['id'] === (int)$recharId && (int)($quota['status'] ?? 1) === 1) $data = $quota;
                    }
                    if (!$data) {
                        throw new ApiException('您选择的充值方式已下架');
                    } else {
                        $paid_price = $data['give_money'] ?? 0;
                        $price = $data['price'] ?? 0;
                    }
                }
                if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string)$price) || bccomp((string)$price, '0', 2) <= 0
                    || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string)$paid_price)
                    || bccomp(bcadd((string)$price, (string)$paid_price, 2), '99999999.99', 2) > 0) {
                    throw new ApiException('充值金额或赠送金额不合法');
                }
                $recharge_data = [];
                $recharge_data['order_id'] = SnowflakeIdGenerator::generate('cz');
                $recharge_data['uid'] = $uid;
                $recharge_data['price'] = $price;
                $recharge_data['recharge_type'] = $from;
                $recharge_data['paid'] = 0;
                $recharge_data['add_time'] = time();
                $recharge_data['give_price'] = $paid_price;
                $recharge_data['channel_type'] = $user['user_type'];
                if (!$rechargeOrder = $this->dao->save($recharge_data)) {
                    throw new ApiException('充值订单生成失败');
                }
                try {
                    /** @var RechargeServices $recharge */
                    $recharge = app()->make(RechargeServices::class);
                    $order_info = $recharge->recharge($rechargeOrder);
                } catch (\Exception $e) {
                    throw new ApiException($e->getMessage());
                }
                if ($renten) {
                    return $order_info;
                }
                return ['msg' => '', 'type' => $from, 'data' => $order_info];
            default:
                throw new ApiException('参数错误');
        }
    }

    public function rechargeSuccess($orderId, array $other = [])
    {
        if (!\crmeb\services\pay\PaymentDispatch::collecting()) {
            return app()->make(PayRecordServices::class)->complete((string)$orderId, 'user_recharge',
                (string)($other['trade_no'] ?? ''), fn() => $this->rechargeSuccess($orderId, $other));
        }
        if ($this->dao->be(['order_id' => $orderId, 'paid' => 1])) return true;
        $order = $this->dao->getOne(['order_id' => $orderId, 'paid' => 0]);
        if (!$order) {
            throw new ApiException('订单不存在');
        }
        /** @var \app\services\user\UserServices $userServices */
        $userServices = app()->make(\app\services\user\UserServices::class);
        $user = $userServices->getUserInfo((int)$order['uid']);
        if (!$user) {
            throw new ApiException('用户不存在');
        }
        $price = bcadd((string)$order['price'], (string)$order['give_price'], 2);
        if (!$this->dao->update($order['id'], ['paid' => 1, 'recharge_type' => $other['pay_type'], 'pay_time' => time(), 'trade_no' => $other['trade_no'] ?? ''], 'id')) {
            throw new ApiException('修改订单失败');
        }
        $now_money = bcadd((string)$user['now_money'], (string)$price, 2);
        /** @var \app\services\user\UserMoneyServices $userMoneyServices */
        $userMoneyServices = app()->make(\app\services\user\UserMoneyServices::class);
        $userMoneyServices->moneyIncome('user_recharge', $user['uid'], ['number' => $price, 'price' => $order['price'], 'give_price' => $order['give_price']], $order['id']);

        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        $order['nickname'] = $user['nickname'];
        $order['phone'] = $user['phone'];
        $capitalFlowServices->setFlow($order, 'recharge');

        event('UserRecharge', [
            $order['uid'],
            $order,
            $order['price'],
            $order['give_price'] ?? 0,
            $now_money,
            $user['phone'] ?? ''
        ]);

        $order['pay_type'] = $other['pay_type'];
        $order['payment_context'] = app()->make(PayRecordServices::class)->find((string)$orderId)['context'] ?? [];
        event('OrderShipping', ['recharge', $order, 3, '', '']);

        return true;
    }

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
