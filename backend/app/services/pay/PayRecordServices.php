<?php
declare(strict_types=1);

namespace app\services\pay;

use app\dao\pay\PayRecordDao;
use app\dao\order\OrderCenterDao;
use app\dao\user\UserDao;
use app\dao\user\UserRechargeDao;
use app\dao\user\MemberOrderDao;
use app\dao\user\OfflineReceiptDao;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\PayException;
use think\facade\Log;

/**
 * 一笔订单的支付合同：拉起前记下渠道和金额，通知到来时核对，退款只提交一次。
 */
class PayRecordServices extends BaseService
{
    /** @var PayRecordDao 支付记录 DAO */
    protected $dao;

    /**
     * 初始化。
     * @param PayRecordDao $dao 支付记录 DAO
     */
    public function __construct(PayRecordDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取支付快照。
     * @param string $orderNo 订单号
     * @param bool $lock 是否锁定原支付记录
     * @return array
     */
    public function find(string $orderNo, bool $lock = false): array
    {
        $record = $this->dao->byOrder($orderNo, $lock);
        return $record ? $record->toArray() : [];
    }

    /**
     * 保存下单前的非密钥身份，禁止不同支付合同覆盖。
     * @param string $orderNo 订单号
     * @param string $kind 成功业务
     * @param string $amount 金额
     * @param array $context 支付身份
     * @return void
     */
    public function prepare(string $orderNo, string $kind, string $amount, array $context): void
    {
        $this->releaseStaleLock($orderNo);
        $this->transaction(function () use ($orderNo, $kind, $amount, $context) {
            $order = $this->source($kind, $orderNo, true);
            $sourceAmount = (string)($order[$kind === 'user_recharge' ? 'price' : ($kind === 'offline_scan' ? 'paid_price' : 'pay_price')] ?? '0');
            if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $amount) || bccomp($amount, '0', 2) <= 0
                || bccomp($sourceAmount, $amount, 2) !== 0) {
                throw new PayException('下单金额与业务订单不一致');
            }
            if ((int)($order[$kind === 'product' ? 'pay_status' : 'paid'] ?? 0) !== 0
                || in_array($order['status_code'] ?? '', ['canceled', 'deleted', 'refunded'], true)) {
                throw new PayException('订单已支付或已取消');
            }
            $record = $this->dao->byOrder($orderNo, true);
            if ($record) {
                foreach (['channel_key', 'platform', 'mode', 'mch_id', 'sub_mch_id', 'app_id', 'openid'] as $key) {
                    if (($record['context'][$key] ?? '') !== ($context[$key] ?? '')) {
                        throw new PayException('该订单已使用其他支付身份，请使用原支付方式或取消后重新下单');
                    }
                }
                if ($record['business_type'] !== $kind || bccomp((string)$record['amount'], $amount, 2) !== 0) {
                    throw new PayException('原支付订单金额或业务已变化，请重新下单');
                }
                if ($record['state'] !== 'prepared') throw new PayException('该订单正在处理支付结果，请稍后重试');
                $record->save(['state' => 'creating', 'update_time' => time()]);
            } else {
                $this->dao->save(['order_no' => $orderNo, 'business_type' => $kind,
                    'channel_key' => $context['channel_key'], 'amount' => $amount, 'context' => $context,
                    'state' => 'creating', 'add_time' => time(), 'update_time' => time()]);
            }
            $sources = ['product' => OrderCenterDao::class, 'member' => MemberOrderDao::class];
            if (isset($sources[$kind]) && !app()->make($sources[$kind])->update($order['id'], ['pay_type' => $context['channel_key']])) {
                throw new PayException('订单支付渠道保存失败');
            }
        });
    }

    /**
     * 下单调用结束后释放创建状态，不覆盖已经到达的支付回调。
     * @param string $orderNo 订单号
     * @return void
     */
    public function finishCreation(string $orderNo): void
    {
        $this->dao->getDb()->where('order_no', $orderNo)->where('state', 'creating')
            ->update(['state' => 'prepared', 'update_time' => time()]);
    }

    /**
     * 禁止尚有微信支付合同的订单改用余额等渠道而重复扣款。
     * @param string $orderNo 订单号
     * @param string $channel 目标渠道
     * @return void
     */
    public function assertChannel(string $orderNo, string $channel): void
    {
        $record = $this->find($orderNo);
        if ($record && $record['context'] && $record['channel_key'] !== $channel) {
            throw new PayException('订单已发起微信支付，请使用原支付方式或取消后重新下单');
        }
    }

    /**
     * 获取业务源订单，支付和取消使用同一行锁。
     * @param string $kind 业务类型
     * @param string $orderNo 订单号
     * @param bool $lock 是否加锁
     * @return array
     */
    public function source(string $kind, string $orderNo, bool $lock = false): array
    {
        $sources = ['product' => [OrderCenterDao::class, 'order_no'], 'user_recharge' => [UserRechargeDao::class, 'order_id'],
            'member' => [MemberOrderDao::class, 'order_id'], 'offline_scan' => [OfflineReceiptDao::class, 'order_no']];
        if (!isset($sources[$kind])) throw new PayException('支付业务类型不存在');
        [$class, $field] = $sources[$kind];
        $order = app()->make($class)->getDb()->where($field, $orderNo)->lock($lock)->find();
        if (!$order) throw new PayException('支付订单不存在');
        return $order->toArray();
    }

    /**
     * 核对金额、原渠道、应用、商户与支付者。
     * @param array $order 原订单
     * @param string $kind 业务类型
     * @param string $channel 通知渠道
     * @param array $other 可信通知字段
     * @return string 原渠道
     */
    public function validate(array $order, string $kind, string $channel, array $other): string
    {
        $orderNo = (string)($kind === 'product' || $kind === 'offline_scan' ? $order['order_no'] : $order['order_id']);
        $record = $this->find($orderNo);
        $extra = $order['extra_info'] ?? [];
        if (is_string($extra)) $extra = json_decode($extra, true) ?: [];
        $context = $record['context'] ?? $extra['payment'] ?? [];
        $mode = $other['payment_mode'] ?? '';
        if ($mode === 'mini_shop') {
            if (!$context || ($context['mode'] ?? '') !== 'mini_shop') throw new PayException('新版支付通知缺少原支付身份');
            $channel = (string)$context['channel_key'];
        }
        if (!in_array($channel, ['weixin', 'weixin_v3'], true)) return $channel;
        $price = (string)($order[$kind === 'user_recharge' ? 'price' : ($kind === 'offline_scan' ? 'paid_price' : 'pay_price')] ?? '');
        $amount = $other['amount'] ?? [];
        if (!isset($amount['total']) || !is_int($amount['total']) || $amount['total'] <= 0
            || $amount['total'] !== (int)bcmul($price, '100', 0) || ($amount['currency'] ?? '') !== 'CNY') {
            throw new PayException('支付通知金额或币种不一致');
        }
        if (($record && ($record['business_type'] !== $kind || bccomp((string)$record['amount'], $price, 2) !== 0))
            || (!empty($context['channel_key']) && $context['channel_key'] !== $channel)
            || (!empty($context['mode']) && $context['mode'] !== $mode)
            || (!empty($order['pay_type']) && $order['pay_type'] !== $channel)
            || ($kind === 'user_recharge' && $order['recharge_type'] !== $channel)) {
            throw new PayException('支付通知与原订单渠道不一致');
        }
        foreach (['mch_id' => 'mchid', 'sub_mch_id' => 'sub_mchid', 'app_id' => 'appid', 'openid' => 'openid'] as $key => $field) {
            if (array_key_exists($key, $context) && ($key === 'sub_mch_id' || $context[$key] !== '')
                && (string)$context[$key] !== (string)($other[$field] ?? '')) {
                throw new PayException('支付通知与原商户、应用或支付者不一致');
            }
        }
        // 历史订单没有快照时仍核对当前商户，不能省略归属校验。
        if (!$context) {
            $config = \crmeb\services\pay\PayChannelConfig::get($channel);
            if (($config['pay_weixin_mchid'] ?? '') !== ($other['mchid'] ?? '')
                || (!empty($config['mer_type']) && ($config['pay_sub_merchant_id'] ?? '') !== ($other['sub_mchid'] ?? ''))) {
                throw new PayException('支付通知商户不一致');
            }
        }
        return $channel;
    }

    /**
     * 有微信流水时把合同标成已完成，不写后置任务。
     * @param string $orderNo 订单号
     * @param string $tradeNo 微信流水
     * @return void
     */
    public function markCompleted(string $orderNo, string $tradeNo): void
    {
        $record = $this->dao->byOrder($orderNo, true);
        if (!$record || $tradeNo === '') {
            return;
        }
        if ((string)$record['transaction_id'] !== '' && $record['transaction_id'] !== $tradeNo) {
            throw new PayException('支付流水与原订单不一致');
        }
        $context = is_array($record['context']) ? $record['context'] : [];
        $context['transaction_id'] = $tradeNo;
        $record->save([
            'state' => 'completed',
            'transaction_id' => $tradeNo,
            'context' => $context,
            'effects' => [],
            'effects_state' => '',
            'update_time' => time(),
        ]);
    }

    /**
     * 下单或关单写到一半时，超过 5 分钟改回可继续；没超过就拒绝。
     * @param string $orderNo 订单号
     * @return void
     */
    public function releaseStaleLock(string $orderNo): void
    {
        $record = $this->find($orderNo);
        if (!in_array($record['state'] ?? '', ['creating', 'closing'], true)) {
            return;
        }
        if ($this->recordTimestamp($record) > time() - 300) {
            throw new PayException('订单正在处理支付结果，请稍后重试');
        }
        $this->dao->getDb()->where('order_no', $orderNo)->where('state', $record['state'])
            ->update(['state' => 'prepared', 'update_time' => time()]);
    }

    /**
     * 保存取消后实付的补偿意图，不复活已经释放库存的订单。
     * @param string $orderNo 原订单号
     * @param string $tradeNo 微信流水
     * @return bool
     */
    public function compensateCanceled(string $orderNo, string $tradeNo, array $notice = []): bool
    {
        $record = $this->dao->byOrder($orderNo, true);
        if ($record && in_array($record['state'], ['compensating', 'compensated'], true)) return true;
        $amount = $this->noticeAmount($notice, $record ? (string)$record['amount'] : '0.00');
        $context = ($record && is_array($record['context']) && $record['context']) ? $record['context'] : [
            'channel_key' => (string)($notice['pay_type'] ?? 'weixin'),
            'mch_id' => (string)($notice['mchid'] ?? ''),
            'sub_mch_id' => (string)($notice['sub_mchid'] ?? ''),
            'app_id' => (string)($notice['appid'] ?? ''),
        ];
        $context['transaction_id'] = $tradeNo;
        $refund = ['refund_no' => 'tk' . substr(hash('sha256', $orderNo), 0, 28),
            'amount' => $amount, 'kind' => 'canceled', 'submitted_at' => 0];
        $data = ['state' => 'compensating', 'context' => $context, 'transaction_id' => $tradeNo,
            'channel_key' => (string)($context['channel_key'] ?? 'weixin'),
            'amount' => $amount, 'refund_state' => 'PENDING', 'refund' => $refund, 'update_time' => time()];
        if ($record) {
            $record->save($data);
            return true;
        }
        $data['order_no'] = $orderNo;
        $data['business_type'] = 'product';
        $data['add_time'] = time();
        $this->dao->save($data);
        return true;
    }

    /**
     * 从通知金额或原记录取出待退金额。
     * @param array $notice 支付通知
     * @param string $fallback 原记录金额
     * @return string
     */
    protected function noticeAmount(array $notice, string $fallback): string
    {
        $total = $notice['amount']['total'] ?? null;
        if (is_int($total) && $total > 0) {
            return bcdiv((string)$total, '100', 2);
        }
        return $fallback !== '' ? $fallback : '0.00';
    }

    /**
     * 支付记录上的更新时间。模型可能把整型时间读成日期字符串。
     * @param array $record 支付记录
     * @return int
     */
    protected function recordTimestamp(array $record): int
    {
        $updated = $record['update_time'] ?? 0;
        if (is_numeric($updated)) {
            return (int)$updated;
        }
        $parsed = strtotime((string)$updated);
        return $parsed === false ? 0 : $parsed;
    }

    /**
     * 已取消但仍收款、且还没点过退款的记录，不由定时任务自动原路退。
     * @param array $record 支付记录
     * @return bool
     */
    public function deferCanceledRefund(array $record): bool
    {
        $refund = is_array($record['refund'] ?? null) ? $record['refund'] : [];
        return ($record['state'] ?? '') === 'compensating'
            && ($refund['kind'] ?? '') === 'canceled'
            && ($record['refund_state'] ?? '') === 'PENDING';
    }

    /**
     * 列出仍等待管理端点一次原路退款的订单号。
     * @param array $orderNos 订单号
     * @return array<string, int>
     */
    public function pendingCanceledRefundMap(array $orderNos): array
    {
        $orderNos = array_values(array_unique(array_filter($orderNos)));
        if (!$orderNos) {
            return [];
        }
        $rows = $this->dao->getDb()->whereIn('order_no', $orderNos)
            ->where('state', 'compensating')
            ->where('refund_state', 'PENDING')
            ->field('order_no,state,refund_state,refund')
            ->select();
        $map = [];
        foreach ($rows as $row) {
            $data = is_array($row) ? $row : $row->toArray();
            if ($this->deferCanceledRefund($data)) {
                $map[(string)$data['order_no']] = 1;
            }
        }
        return $map;
    }

    public function submitCanceledRefund(string $orderNo): bool
    {
        $record = $this->find($orderNo);
        $refund = is_array($record['refund'] ?? null) ? $record['refund'] : [];
        if (!$record || $record['state'] !== 'compensating' || ($refund['kind'] ?? '') !== 'canceled') {
            throw new PayException('没有待退的取消收款');
        }
        if (!in_array($record['refund_state'], ['PENDING', 'SUBMITTING'], true)) {
            return true;
        }
        $this->submitRefundOnce($orderNo);
        return true;
    }

    /**
     * 充值退款先在事务内冻结余额，避免退款过程中余额再次消费。
     * @param int $id 充值记录ID
     * @param bool $deductGift 是否同时扣赠送余额
     * @param bool $allowShortfall 余额不足时仍继续退款，只扣光当前余额
     * @return bool
     */
    public function refundRecharge(int $id, bool $deductGift, bool $allowShortfall = false): bool
    {
        $orderNo = (string)app()->make(UserRechargeDao::class)->value(['id' => $id], 'order_id');
        if (!$orderNo) throw new PayException('充值记录不存在');
        $this->transaction(function () use ($orderNo, $deductGift, $allowShortfall, $id) {
            $order = $this->source('user_recharge', $orderNo, true);
            if (!(int)$order['paid']) throw new PayException('订单未支付');
            if ($order['recharge_type'] === 'balance') throw new PayException('佣金转入余额不能原路退款');
            if (bccomp((string)$order['refund_price'], (string)$order['price'], 2) >= 0) throw new PayException('充值已退款');
            $record = $this->dao->byOrder($orderNo, true);
            if ($record && $record['refund_state'] !== '') return;
            $number = $deductGift ? bcadd((string)$order['price'], (string)$order['give_price'], 2) : (string)$order['price'];
            if ($allowShortfall) {
                $lockedUser = app()->make(UserDao::class)->getDb()->where('uid', (int)$order['uid'])->lock(true)->field('uid,now_money')->find();
                if (!$lockedUser) throw new PayException('用户不存在');
                $balance = (string)$lockedUser['now_money'];
                if (bccomp($balance, $number, 2) < 0) {
                    $number = bccomp($balance, '0', 2) > 0 ? $balance : '0';
                }
            }
            $debited = false;
            if (bccomp($number, '0', 2) > 0) {
                // 未允许缺口时，余额不足会在扣款处中断，避免先退本金再留下无法追偿的差额。
                app()->make(UserMoneyServices::class)->moneyIncome('user_recharge_refund',
                    (int)$order['uid'], $number, $id);
                $debited = true;
            }
            $refund = ['refund_no' => 'tk' . substr(hash('sha256', 'recharge:' . $orderNo), 0, 28),
                'amount' => $order['price'], 'number' => $number, 'kind' => 'recharge', 'uid' => $order['uid'],
                'source_id' => $id, 'submitted_at' => 0, 'debited' => $debited];
            $data = ['refund' => $refund, 'refund_state' => 'PENDING', 'update_time' => time()];
            if ($record) {
                $record->save($data);
            } else {
                $this->dao->save($data + ['order_no' => $orderNo, 'business_type' => 'user_recharge',
                    'channel_key' => $order['recharge_type'], 'amount' => $order['price'], 'transaction_id' => $order['trade_no'],
                    'state' => 'completed', 'add_time' => time()]);
            }
        });
        $this->submitRefundOnce($orderNo);
        return true;
    }

    /**
     * 用原退款号向微信提交一次。当场成功或失败就写本地，其余等退款通知。
     * @param string $orderNo 原订单号
     * @return void
     */
    public function submitRefundOnce(string $orderNo): void
    {
        $record = $this->find($orderNo);
        if (!in_array($record['refund_state'] ?? '', ['PENDING', 'SUBMITTING'], true)) {
            return;
        }
        if ($record['refund_state'] === 'PENDING') {
            $changed = $this->dao->getDb()->where('order_no', $orderNo)->where('refund_state', 'PENDING')
                ->update(['refund_state' => 'SUBMITTING', 'update_time' => time()]);
            if (!$changed) {
                return;
            }
        }
        $refund = $record['refund'];
        $result = app()->make(PayServices::class)->submitRefund(
            $record['channel_key'],
            $orderNo,
            $refund['refund_no'],
            (string)$refund['amount'],
            (string)$record['amount'],
            ['extra_info' => ['payment' => $record['context'] ?? []], 'trade_no' => $record['transaction_id']]
        );
        if (!in_array($result['status'], [PayServices::REFUND_SUCCESS, PayServices::REFUND_FAILED], true)) {
            return;
        }
        $this->transaction(function () use ($orderNo, $result) {
            $snapshot = $this->find($orderNo);
            $this->source($snapshot['business_type'], $orderNo, true);
            $locked = $this->dao->byOrder($orderNo, true);
            $this->finishRefund($locked, $result['status'], (string)($result['channel_refund_id'] ?? ''));
        });
        if ($result['status'] === PayServices::REFUND_FAILED) {
            throw new PayException($result['message'] !== '' ? $result['message'] : '原路退款失败');
        }
    }

    /**
     * 退款通知核对后退款号、流水和金额，再写本地结果。不一致则抛错，通知按失败返回。
     * @param string $orderNo 原订单号
     * @param array $notify 已验签的退款通知
     * @param string $payChannel 通知渠道
     * @return bool 本地完成才确认通知
     */
    public function applyRefundNotify(string $orderNo, array $notify, string $payChannel): bool
    {
        try {
            $this->transaction(function () use ($orderNo, $notify, $payChannel): void {
                $snapshot = $this->find($orderNo);
                if (!$snapshot) {
                    throw new PayException('退款通知缺少原支付合同');
                }
                $this->source($snapshot['business_type'], $orderNo, true);
                $record = $this->dao->byOrder($orderNo, true);
                $context = is_array($record['context'] ?? null) ? $record['context'] : [];
                $refund = is_array($record['refund'] ?? null) ? $record['refund'] : [];
                $amount = $notify['amount'] ?? [];
                foreach (['refund', 'total'] as $moneyKey) {
                    if (is_string($amount[$moneyKey] ?? null) && preg_match('/^\d+$/D', $amount[$moneyKey])) {
                        $amount[$moneyKey] = (int)$amount[$moneyKey];
                    }
                }
                $notifyAppId = !empty($context['sub_mch_id']) ? ($notify['sub_appid'] ?? '') : ($notify['appid'] ?? '');
                $mode = (string)($context['mode'] ?? '');
                $v2 = $mode === 'v2' || ($notify['payment_mode'] ?? '') === 'v2';
                if ($record['channel_key'] !== $payChannel
                    || ($notify['refund_status'] ?? '') !== 'SUCCESS' || empty($notify['refund_id'])
                    || empty($refund['refund_no']) || $refund['refund_no'] !== ($notify['out_refund_no'] ?? '')
                    || ($context['mch_id'] ?? '') !== ($notify['mchid'] ?? '')
                    || ($context['sub_mch_id'] ?? '') !== ($notify['sub_mchid'] ?? '')
                    || (!empty($refund['channel_refund_id']) && $refund['channel_refund_id'] !== $notify['refund_id'])
                    || !is_int($amount['refund'] ?? null) || !is_int($amount['total'] ?? null)
                    || $amount['refund'] !== (int)bcmul((string)$refund['amount'], '100', 0)
                    || $amount['total'] !== (int)bcmul((string)$record['amount'], '100', 0)
                    || ($v2 && ($payChannel !== PayServices::WEIXIN_PAY
                        || ($notify['payment_mode'] ?? '') !== 'v2'
                        || ((empty($context['sub_mch_id']) || $notifyAppId !== '') && ($context['app_id'] ?? '') !== $notifyAppId)
                        || empty($record['transaction_id']) || $record['transaction_id'] !== ($notify['transaction_id'] ?? '')))) {
                    throw new PayException('退款通知与支付合同、流水或金额不一致');
                }
                $this->finishRefund($record, 'SUCCESS', (string)$notify['refund_id']);
            });
            return !empty($this->find($orderNo)['refund']['applied']);
        } catch (\Throwable $e) {
            Log::error('[PayRefund] 退款通知未写入 type=' . get_class($e));
            return false;
        }
    }

    /**
     * 把渠道退款结果写入本地流水或取消补偿。调用时合同行已锁定。
     * @param mixed $record 锁定的支付记录
     * @param string $status 渠道结果
     * @param string $channelRefundId 微信退款号
     * @return void
     */
    protected function finishRefund($record, string $status, string $channelRefundId = ''): void
    {
        $refund = is_array($record['refund'] ?? null) ? $record['refund'] : [];
        if (!empty($refund['applied'])) {
            return;
        }
        $orderNo = (string)$record['order_no'];
        $refund['status'] = $status;
        if ($status === PayServices::REFUND_SUCCESS) {
            if (($refund['kind'] ?? '') === 'recharge') {
                $order = $this->source('user_recharge', $orderNo, true);
                app()->make(UserRechargeDao::class)->update($order['id'], ['refund_price' => $refund['amount']]);
                $user = app()->make(UserServices::class)->getUserInfo((int)$order['uid']);
                $order['nickname'] = $user['nickname'];
                $order['phone'] = $user['phone'];
                app()->make(CapitalFlowServices::class)->setFlow($order, 'refund_recharge');
                event('UserRechargeRefund', ['recharge_order_refund_status', ['uid' => $order['uid'],
                    'amount' => $refund['amount'], 'order_id' => $orderNo, 'user_type' => $order['channel_type']]]);
                event('UserRechargeRefund', ['recharge_refund', ['uid' => $order['uid'], 'order_id' => $orderNo, 'price' => $refund['amount']]]);
            } else {
                app()->make(OrderCenterDao::class)->update(['order_no' => $orderNo], ['pay_status' => 3, 'refund_status' => 2]);
            }
            $refund['applied'] = true;
            if ($channelRefundId !== '') {
                $refund['channel_refund_id'] = $channelRefundId;
            }
        } elseif ($status === PayServices::REFUND_FAILED && ($refund['kind'] ?? '') === 'recharge' && !empty($refund['debited'])) {
            app()->make(UserMoneyServices::class)->moneyIncome('user_recharge_refund_release',
                (int)$refund['uid'], (string)$refund['number'], $refund['source_id']);
            $refund['debited'] = false;
        }
        $record->save([
            'refund_state' => $status,
            'refund' => $refund,
            'update_time' => time(),
            'state' => ($refund['kind'] ?? '') === 'canceled' && !empty($refund['applied']) ? 'compensated' : $record['state'],
        ]);
    }

    /**
     * 取消前核对是否已经支付，以及核对新版小程序通知。
     * @param string $orderNo 原订单号
     * @param string $expectedTradeNo 通知流水（主动查单时可为空）
     * @param string $expectedOpenid 通知支付者（主动查单时可为空）
     * @return bool
     */
    public function syncPayment(string $orderNo, string $expectedTradeNo = '', string $expectedOpenid = ''): bool
    {
        $record = $this->find($orderNo);
        if (!$record || !$record['context']) throw new PayException('查单缺少原支付合同');
        $context = $record['context'];
        $pay = app()->make(\crmeb\services\pay\Pay::class, [app()->make(PayServices::class)->getDriverByChannelKey($record['channel_key'])]);
        $result = $pay->queryPayment($orderNo, $context);
        $mini = $context['mode'] === 'mini_shop';
        $tradeNo = (string)($result['transaction_id'] ?? '');
        $openid = (string)($mini ? ($result['openid'] ?? '') : ($result['sub_openid'] ?? $result['openid']
            ?? $result['payer']['sub_openid'] ?? $result['payer']['openid'] ?? ''));
        if (($expectedTradeNo !== '' && $tradeNo !== $expectedTradeNo)
            || ($expectedOpenid !== '' && $openid !== $expectedOpenid)) throw new PayException('支付通知与查单流水或支付者不一致');
        if ($mini) {
            // 无可靠终态字段时只处理已验签的 funds_order_pay 通知，不凭订单存在推断付款。
            if ($expectedTradeNo === '') return false;
            if (!$tradeNo || !isset($result['amount'])) return false;
            $amount = ['total' => is_int($result['amount']) ? $result['amount'] : null, 'currency' => 'CNY'];
        } else {
            if (($result['trade_state'] ?? '') !== 'SUCCESS') return false;
            $amount = $context['mode'] === 'v2' ? ['total' => isset($result['total_fee']) ? (int)$result['total_fee'] : null,
                'currency' => $result['fee_type'] ?? 'CNY'] : ($result['amount'] ?? []);
        }
        $method = ['product' => 'wechatProduct', 'user_recharge' => 'wechatUserRecharge', 'member' => 'wechatMember', 'offline_scan' => 'wechatOfflineScan'][$record['business_type']];
        return app()->make(PayNotifyServices::class)->$method($orderNo, $tradeNo, $record['channel_key'], [
            'payment_mode' => $context['mode'], 'amount' => $amount, 'openid' => $openid,
            'mchid' => $result['mchid'] ?? $result['mch_id'] ?? $result['sp_mchid'] ?? '',
            'sub_mchid' => $result['sub_mchid'] ?? $result['sub_mch_id'] ?? '',
            'appid' => $mini ? $context['app_id'] : ($result['sub_appid'] ?? $result['appid'] ?? $result['sp_appid'] ?? ''),
        ]);
    }

    /**
     * 取消前关闭普通微信订单；未知结果保持订单未取消。
     * @param string $orderNo 原订单号
     * @return void
     */
    public function closeBeforeCancel(string $orderNo): void
    {
        $this->releaseStaleLock($orderNo);
        $record = $this->find($orderNo);
        if (!$record || empty($record['context'])) return;
        if ($record['state'] === 'closed') return;
        if (in_array($record['state'], ['prepared', 'creating'], true) && $this->recordTimestamp($record) > time() - 5) {
            throw new PayException('支付处理中，请稍后再取消');
        }
        $claimed = $this->dao->getDb()->where('order_no', $orderNo)->where('state', 'prepared')
            ->update(['state' => 'closing', 'update_time' => time()]);
        if (!$claimed) throw new PayException('订单正在下单或处理支付结果，请稍后取消');
        try {
            if ($this->syncPayment($orderNo)) throw new PayException('订单已经支付，不能取消');
            $pay = app()->make(\crmeb\services\pay\Pay::class, [app()->make(PayServices::class)->getDriverByChannelKey($record['channel_key'])]);
            if (!$pay->closePayment($orderNo, $record['context'])) throw new PayException('微信订单未能关闭，请稍后重试');
            $this->dao->getDb()->where('order_no', $orderNo)->where('state', 'closing')
                ->update(['state' => 'closed', 'update_time' => time()]);
        } catch (\Throwable $e) {
            $this->dao->getDb()->where('order_no', $orderNo)->where('state', 'closing')
                ->update(['state' => 'prepared', 'update_time' => time()]);
            throw $e;
        }
    }
}
