<?php
declare(strict_types=1);

namespace app\services\pay;

use app\dao\pay\PayRecordDao;
use app\dao\order\OrderCenterDao;
use app\dao\user\UserRechargeDao;
use app\dao\user\MemberOrderDao;
use app\dao\user\OfflineReceiptDao;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\basic\BaseService;
use crmeb\services\pay\PaymentDispatch;
use crmeb\exceptions\PayException;
use think\facade\Log;

/**
 * 原支付合同、成功事务及可恢复后置任务。
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
     * 在源订单锁和事务内完成关键支付效果，将后置任务可靠写入记录。
     * @param string $orderNo 订单号
     * @param string $kind 业务类型
     * @param string $tradeNo 微信流水
     * @param callable $callback 关键业务处理
     * @return bool
     */
    public function complete(string $orderNo, string $kind, string $tradeNo, callable $callback): bool
    {
        $result = PaymentDispatch::collect(function () use ($orderNo, $kind, $tradeNo, $callback) {
            return $this->transaction(function () use ($orderNo, $kind, $tradeNo, $callback) {
                $order = $this->source($kind, $orderNo, true);
                $record = $this->dao->byOrder($orderNo, true);
                if ($record && !empty($record['transaction_id']) && $record['transaction_id'] !== $tradeNo) {
                    throw new PayException('支付流水与原订单不一致');
                }
                if (!$callback($order)) throw new PayException('支付业务处理失败');
                $record = $this->dao->byOrder($orderNo, true);
                if ($record && in_array($record['state'], ['completed', 'compensating', 'compensated'], true)) return true;
                if ($record) {
                    $context = $record['context'];
                    $context['transaction_id'] = $tradeNo;
                    $record->save(['state' => 'completed', 'transaction_id' => $tradeNo, 'context' => $context,
                        'effects' => PaymentDispatch::tasks(), 'effects_state' => 'pending', 'update_time' => time()]);
                } elseif (PaymentDispatch::tasks()) {
                    $this->dao->save(['order_no' => $orderNo, 'business_type' => $kind, 'state' => 'completed',
                        'transaction_id' => $tradeNo, 'effects' => PaymentDispatch::tasks(), 'effects_state' => 'pending',
                        'add_time' => time(), 'update_time' => time()]);
                }
                return true;
            });
        });
        $this->runEffects($orderNo);
        $this->processRefund($orderNo);
        return $result;
    }

    /**
     * 保存取消后实付的补偿意图，不复活已经释放库存的订单。
     * @param string $orderNo 原订单号
     * @param string $tradeNo 微信流水
     * @return bool
     */
    public function compensateCanceled(string $orderNo, string $tradeNo): bool
    {
        $record = $this->dao->byOrder($orderNo, true);
        if (!$record || empty($record['context'])) throw new PayException('历史取消订单缺少原支付合同，需人工核对退款');
        if (in_array($record['state'], ['compensating', 'compensated'], true)) return true;
        $context = $record['context'];
        $context['transaction_id'] = $tradeNo;
        $record->save(['state' => 'compensating', 'context' => $context, 'transaction_id' => $tradeNo,
            'refund_state' => 'PENDING', 'refund' => ['refund_no' => 'tk' . substr(hash('sha256', $orderNo), 0, 28),
                'amount' => $record['amount'], 'kind' => 'canceled', 'submitted_at' => 0], 'update_time' => time()]);
        return true;
    }

    /**
     * 充值退款先在事务内冻结余额，避免退款过程中余额再次消费。
     * @param int $id 充值记录ID
     * @param bool $deductGift 是否同时扣赠送余额
     * @return bool
     */
    public function refundRecharge(int $id, bool $deductGift): bool
    {
        $orderNo = (string)app()->make(UserRechargeDao::class)->value(['id' => $id], 'order_id');
        if (!$orderNo) throw new PayException('充值记录不存在');
        PaymentDispatch::collect(function () use ($orderNo, $deductGift, $id) {
            $this->transaction(function () use ($orderNo, $deductGift, $id) {
                $order = $this->source('user_recharge', $orderNo, true);
                if (!(int)$order['paid']) throw new PayException('订单未支付');
                if ($order['recharge_type'] === 'balance') throw new PayException('佣金转入余额不能原路退款');
                if (bccomp((string)$order['refund_price'], (string)$order['price'], 2) >= 0) throw new PayException('充值已退款');
                $record = $this->dao->byOrder($orderNo, true);
                if ($record && $record['refund_state'] !== '') return;
                $number = $deductGift ? bcadd((string)$order['price'], (string)$order['give_price'], 2) : (string)$order['price'];
                // 不允许先退本金再让余额形成无法追偿的缺口；不足时由财务人工处理。
                app()->make(UserMoneyServices::class)->moneyIncome('user_recharge_refund',
                    (int)$order['uid'], $number, $id);
                $refund = ['refund_no' => 'tk' . substr(hash('sha256', 'recharge:' . $orderNo), 0, 28),
                    'amount' => $order['price'], 'number' => $number, 'kind' => 'recharge', 'uid' => $order['uid'],
                    'source_id' => $id, 'submitted_at' => 0, 'debited' => true];
                $data = ['refund' => $refund, 'refund_state' => 'PENDING', 'update_time' => time()];
                if ($record) {
                    $record->save($data);
                } else {
                    $this->dao->save($data + ['order_no' => $orderNo, 'business_type' => 'user_recharge',
                        'channel_key' => $order['recharge_type'], 'amount' => $order['price'], 'transaction_id' => $order['trade_no'],
                        'state' => 'completed', 'add_time' => time()]);
                }
                if (PaymentDispatch::tasks()) {
                    $current = $this->dao->byOrder($orderNo, true);
                    $current->save(['effects' => array_merge($current['effects'] ?? [], PaymentDispatch::tasks()), 'effects_state' => 'pending']);
                }
            });
        });
        $this->runEffects($orderNo);
        $this->processRefund($orderNo);
        return true;
    }

    /**
     * 接收驱动查验签名结果后的 V2 退款通知，充值和取消补偿立即完成而不等待查询租约。
     * @param array $notify 已核验退款通知
     * @param string $payChannel 可信驱动渠道
     * @return bool 本地完成才确认通知
     */
    public function handleWechatRefundNotify(array $notify, string $payChannel): bool
    {
        try {
            $orderNo = (string)($notify['out_trade_no'] ?? '');
            $this->transaction(function () use ($orderNo, $notify, $payChannel): void {
                $snapshot = $this->find($orderNo);
                if (!$snapshot) throw new PayException('退款通知缺少原支付合同');
                $this->source($snapshot['business_type'], $orderNo, true);
                $record = $this->dao->byOrder($orderNo, true);
                $context = $record['context'] ?? [];
                $refund = $record['refund'] ?? [];
                $amount = $notify['amount'] ?? [];
                $notifyAppId = !empty($context['sub_mch_id']) ? ($notify['sub_appid'] ?? '') : ($notify['appid'] ?? '');
                if ($payChannel !== PayServices::WEIXIN_PAY || $record['channel_key'] !== $payChannel
                    || ($context['mode'] ?? '') !== 'v2' || ($notify['payment_mode'] ?? '') !== 'v2'
                    || ($notify['refund_status'] ?? '') !== 'SUCCESS' || empty($notify['refund_id'])
                    || empty($refund['refund_no']) || $refund['refund_no'] !== ($notify['out_refund_no'] ?? '')
                    || ($context['mch_id'] ?? '') !== ($notify['mchid'] ?? '')
                    || ($context['sub_mch_id'] ?? '') !== ($notify['sub_mchid'] ?? '')
                    || ((empty($context['sub_mch_id']) || $notifyAppId !== '') && ($context['app_id'] ?? '') !== $notifyAppId)
                    || empty($record['transaction_id']) || $record['transaction_id'] !== ($notify['transaction_id'] ?? '')
                    || (!empty($refund['channel_refund_id']) && $refund['channel_refund_id'] !== $notify['refund_id'])
                    || !is_int($amount['refund'] ?? null) || !is_int($amount['total'] ?? null)
                    || $amount['refund'] !== (int)bcmul((string)$refund['amount'], '100', 0)
                    || $amount['total'] !== (int)bcmul((string)$record['amount'], '100', 0)) {
                    throw new PayException('V2退款通知与支付合同、流水或金额不一致');
                }
                if (!empty($refund['applied'])) return;
                $refund['channel_refund_id'] = $notify['refund_id'];
                $record->save(['refund_state' => 'SUCCESS', 'refund' => $refund, 'update_time' => time()]);
            });
            $this->processRefund($orderNo);
            return !empty($this->find($orderNo)['refund']['applied']);
        } catch (\Throwable $e) {
            Log::error('[WechatV2Refund] 本地退款处理失败 type=' . get_class($e));
            return false;
        }
    }

    /**
     * 提交或查询同一固定退款号，未知结果绝不换号重提。
     * @param string $orderNo 原订单号
     * @return void
     */
    public function processRefund(string $orderNo): void
    {
        $record = $this->find($orderNo);
        if (!$record || !in_array($record['refund_state'], ['PENDING', 'SUBMITTING', 'UNKNOWN', 'PROCESSING', 'SUCCESS'], true)) return;
        $refund = $record['refund'];
        $pay = app()->make(PayServices::class);
        $info = ['extra_info' => ['payment' => $record['context'] ?? []], 'trade_no' => $record['transaction_id']];
        if ($record['refund_state'] === 'PENDING') {
            $changed = $this->dao->getDb()->where('order_no', $orderNo)->where('refund_state', 'PENDING')
                ->update(['refund_state' => 'SUBMITTING', 'update_time' => time()]);
            if (!$changed) return;
            $result = $pay->submitRefund($record['channel_key'], $orderNo, $refund['refund_no'],
                (string)$refund['amount'], (string)$record['amount'], $info);
        } elseif ($record['refund_state'] === 'SUCCESS') {
            $result = ['status' => 'SUCCESS'];
        } else {
            if ($record['refund_state'] === 'SUBMITTING' && (int)$record['update_time'] > time() - 120) return;
            $result = $pay->queryRefundStatus($record['channel_key'], $orderNo, $refund['refund_no'], ['order_info' => $info]);
        }
        // 对微信到账查询结果和本地余额/流水效果使用同一事务边界。
        PaymentDispatch::collect(function () use ($orderNo, $result) {
            $this->transaction(function () use ($orderNo, $result) {
                $snapshot = $this->find($orderNo);
                $this->source($snapshot['business_type'], $orderNo, true);
                $record = $this->dao->byOrder($orderNo, true);
                $refund = $record['refund'];
                if (!empty($refund['applied'])) return;
                $refund['status'] = $result['status'];
                $refund['message'] = (string)($result['message'] ?? '');
                $status = $result['status'];
                // 先保留渠道成功信息，后续本地失败仍可查询同一退款恢复。
                if ($status === 'SUCCESS') {
                    if ($refund['kind'] === 'recharge') {
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
                } elseif ($status === 'FAILED' && $refund['kind'] === 'recharge' && !empty($refund['debited'])) {
                    app()->make(UserMoneyServices::class)->moneyIncome('user_recharge_refund_release',
                        (int)$refund['uid'], (string)$refund['number'], $refund['source_id']);
                    $refund['debited'] = false;
                }
                $effects = array_merge($record['effects'] ?? [], PaymentDispatch::tasks());
                $record->save(['refund_state' => $status, 'refund' => $refund, 'effects' => $effects,
                    'effects_state' => $effects ? 'pending' : ($record['effects_state'] ?? ''), 'update_time' => time(),
                    'state' => $refund['kind'] === 'canceled' && !empty($refund['applied']) ? 'compensated' : $record['state']]);
            });
        });
        $this->runEffects($orderNo);
    }

    /**
     * 分批恢复漏单、崩溃后租约、退款及后置任务，只使用既有合同和退款号。
     * @param int $limit 批量上限
     * @return array 汇总，不包含凭证及个人信息
     */
    public function recover(int $limit = 100): array
    {
        $stats = ['checked' => 0, 'failed' => 0];
        foreach ($this->dao->recoverable(max(1, min(100, $limit))) as $orderNo) {
            ++$stats['checked'];
            try {
                $record = $this->find($orderNo);
                // 微信请求超时上限远小于此租约；刚开始的下单/关单不可抢占。
                if (in_array($record['state'], ['creating', 'closing'], true)) {
                    if ((int)$record['update_time'] > time() - 300) continue;
                    $this->dao->getDb()->where('order_no', $orderNo)->where('state', $record['state'])
                        ->where('update_time', $record['update_time'])->update(['state' => 'prepared', 'update_time' => time()]);
                }
                if (in_array($record['state'], ['prepared', 'creating', 'closing', 'received'], true) && $record['context']) {
                    $this->syncPayment($orderNo);
                }
                $this->processRefund($orderNo);
                $this->runEffects($orderNo);
                $latest = $this->find($orderNo);
                if (($latest['effects_state'] ?? '') === 'pending' || ($latest['refund_state'] ?? '') === 'FAILED') ++$stats['failed'];
            } catch (\Throwable $e) {
                ++$stats['failed'];
                Log::error('[PayRecord] 恢复待重试 order_no=' . $orderNo . ' type=' . get_class($e));
            } finally {
                // 更新检查时间，避免长期未付单占据批次头部饿死其他记录。
                $this->dao->getDb()->where('order_no', $orderNo)->update(['last_check_time' => time()]);
            }
        }
        return $stats;
    }

    /**
     * 主动查单恢复或核对新版小程序通知。
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
        $record = $this->find($orderNo);
        if (!$record || empty($record['context'])) return;
        if ($record['state'] === 'closed') return;
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

    /**
     * 提交后逐项执行后置任务，失败保留未执行项，租约防止并发重放。
     * @param string $orderNo 订单号
     * @return void
     */
    public function runEffects(string $orderNo): void
    {
        $token = bin2hex(random_bytes(16));
        $claimed = $this->dao->getDb()->where('order_no', $orderNo)->where('effects_state', 'pending')
            ->where('effects_lock_until', '<', time())->update(['effects_token' => $token, 'effects_lock_until' => time() + 300]);
        if (!$claimed) return;
        try {
            $record = $this->find($orderNo);
            $tasks = $record['effects'] ?? [];
            $result = $this->processEffectTasks($tasks, function (array $task): bool {
                if ($task['type'] === 'event') {
                    return PaymentDispatch::replay(fn () => event($task['event'], $task['params'])) !== false;
                }
                if ($task['type'] === 'listener') {
                    return PaymentDispatch::replay(fn () => app()->make($task['class'])->handle($task['params'])) !== false;
                }
                $class = $task['class'];
                if (!is_subclass_of($class, \crmeb\basic\BaseJobs::class)) {
                    throw new \RuntimeException('支付后置任务类型不正确');
                }
                return PaymentDispatch::replay(fn () => $class::runPaymentTask($task['action'], $task['data'])) !== false;
            }, function (array $remaining) use ($orderNo, $token): bool {
                $db = $this->dao->getDb();
                $updated = $db->where('order_no', $orderNo)->where('effects_token', $token)
                    ->update(['effects' => json_encode($remaining, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'effects_state' => 'pending', 'effects_lock_until' => time() + 300, 'update_time' => time()]);
                if ($updated) return true;

                // MySQL may report 0 when the serialized state is unchanged; verify ownership
                // before treating it as a concurrent replay failure.
                return (bool)$this->dao->getDb()->where('order_no', $orderNo)->where('effects_token', $token)->count();
            });

            foreach ($result['failed'] as $failedTask) {
                Log::error('[PayRecord] 单项支付后置任务失败 type=' . ($failedTask['type'] ?? 'unknown')
                    . ' class=' . ($failedTask['class'] ?? ($failedTask['event'] ?? '')));
            }

            $remaining = $result['tasks'];
            if ($remaining) return;
            $this->dao->getDb()->where('order_no', $orderNo)->where('effects_token', $token)
                ->update(['effects' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'effects_state' => 'completed', 'effects_lock_until' => time() + 300, 'update_time' => time()]);
        } catch (\Throwable $e) {
            Log::error('[PayRecord] 支付后置任务待恢复 order_no=' . $orderNo . ' type=' . get_class($e));
        } finally {
            $this->dao->getDb()->where('order_no', $orderNo)->where('effects_token', $token)
            ->update(['effects_lock_until' => 0, 'effects_token' => '']);
        }
    }

    /**
     * 逐项执行支付后置任务；单项失败或延迟不阻塞后续任务。
     * 失败任务保留在队列中，供后续恢复流程重试。
     *
     * @param array<int, array<string, mixed>> $tasks
     * @param callable(array<string, mixed>): bool $executor
     * @param callable(array): bool|null $onProgress
     * @return array{tasks: array, failed: array, succeeded: int, deferred: int}
     */
    protected function processEffectTasks(array $tasks, callable $executor, ?callable $onProgress = null): array
    {
        $remaining = [];
        $failed = [];
        $succeeded = 0;
        $deferred = 0;

        foreach ($tasks as $task) {
            if ((int)($task['available_at'] ?? 0) > time()) {
                $remaining[] = $task;
                ++$deferred;
                if ($onProgress && $onProgress(array_merge($failed, $remaining)) === false) {
                    throw new \RuntimeException('支付后置任务状态保存失败');
                }
                continue;
            }
            try {
                if (!$executor($task)) {
                    throw new \RuntimeException('支付后置任务执行失败');
                }
                ++$succeeded;
            } catch (\Throwable $e) {
                $failed[] = $task;
            }
            if ($onProgress && $onProgress(array_merge($failed, $remaining)) === false) {
                throw new \RuntimeException('支付后置任务状态保存失败');
            }
        }

        return [
            'tasks' => array_merge($failed, $remaining),
            'failed' => $failed,
            'succeeded' => $succeeded,
            'deferred' => $deferred,
        ];
    }

}
