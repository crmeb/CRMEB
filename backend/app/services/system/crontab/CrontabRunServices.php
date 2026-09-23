<?php
namespace app\services\system\crontab;

use crmeb\crontab\CrontabTaskRegistry;
use app\services\order\OrderCenterServices;
use app\services\order\OrderInvoiceServices;
use app\services\order\OrderTakeServices;
use app\services\order\RefundOrderServices;
use app\services\pay\PayRecordServices;
use app\services\product\CenterProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use think\facade\Log;

class CrontabRunServices
{
    protected $markListCache = null;

    public function getMarkList(): array
    {
        if ($this->markListCache === null) {
            $registry = app()->make(CrontabTaskRegistry::class);
            $registryList = $registry->allInfo();
            $localList = $this->getLocalMarkList();
            $this->markListCache = array_merge($localList, $registryList);
        }
        return $this->markListCache;
    }

    protected function getLocalMarkList(): array
    {
        return [
            'orderCancel' => [
                'name' => '未支付自动取消订单',
                'description' => '订单超过系统设置的未支付时间后自动取消，释放库存和优惠券',
            ],
            'takeDelivery' => [
                'name' => '订单自动收货',
                'description' => '订单发货后超过系统设置的自动收货天数，自动确认收货并完成订单',
            ],
            'productReplay' => [
                'name' => '订单商品自动好评',
                'description' => '订单完成后自动给商品好评',
            ],
            'clearPoster' => [
                'name' => '清除昨日海报',
                'description' => '自动清除昨日生成的海报图片，释放存储空间',
            ],
            'autoInvoice' => [
                'name' => '自动开具发票以及退款自动冲红',
                'description' => '自动开具电子发票，退款时自动冲红已开具的发票',
            ],
            'refundRecover' => [
                'name' => '退款一致性恢复',
                'description' => '查询支付渠道未决退款，并重试渠道成功后的本地产品效果',
            ],
            'paymentRecover' => [
                'name' => '微信支付一致性恢复',
                'description' => '查询既有支付合同并恢复充值退款、取消补偿及后置任务',
            ],
            'customTimer' => [
                'name' => '自定义定时任务',
                'description' => '执行管理员自定义的PHP代码',
            ],
            'noticeUnpaidOrder' => [
                'name' => '未支付订单提醒',
                'description' => '定时提醒用户支付未支付订单',
            ],
            'noticeMemberExpire' => [
                'name' => '会员到期提醒',
                'description' => '定时提醒即将到期的会员用户',
            ],
        ];
    }

    public function getAllTasksInfo(): array
    {
        $registry = app()->make(CrontabTaskRegistry::class);
        $registryList = $registry->allFullInfo();
        $localList = $this->getLocalMarkList();

        $result = [];
        foreach ($localList as $mark => $info) {
            $result[] = [
                'mark' => $mark,
                'name' => $info['name'],
                'description' => $info['description'],
                'class' => static::class . '::' . $mark,
            ];
        }
        foreach ($registryList as $item) {
            $result[] = $item;
        }
        return $result;
    }

    public function __call($name, $arguments)
    {
        $this->crontabLog($name . '方法不存在');
    }

    protected function crontabLog($msg)
    {
        $timer_log_open = config("log.timer_log", false);
        if ($timer_log_open) {
            $date = date('Y-m-d H:i:s', time());
            Log::write($date . $msg, 'crontab');
        }
    }

    public function run(int $crontabId, string $mark, ...$args): void
    {
        $markList = $this->getMarkList();
        $crontabName = $markList[$mark]['name'] ?? $mark;
        $executeTime = date('Y-m-d H:i:s');
        $startTime = microtime(true);

        event('CrontabBeforeRun', [$crontabId, $crontabName, $mark]);

        try {
            $registry = app()->make(CrontabTaskRegistry::class);
            if ($registry->has($mark)) {
                $handler = $registry->get($mark);
                $handler->handle();
            } elseif (method_exists($this, $mark)) {
                call_user_func_array([$this, $mark], $args);
            } else {
                $this->crontabLog($mark . '方法不存在');
                throw new \RuntimeException($mark . '方法不存在');
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            event('CrontabAfterRun', [$crontabId, $crontabName, $mark, $executeTime, $duration]);
            event('CrontabSuccess', [$crontabId, $crontabName, $mark, $executeTime, $duration]);
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            event('CrontabAfterRun', [$crontabId, $crontabName, $mark, $executeTime, $duration]);
            event('CrontabFail', [$crontabId, $crontabName, $mark, $executeTime, $e->getMessage()]);
            throw $e;
        }
    }

    public function orderCancel()
    {
        try {
            // 未支付自动取消已由 modules/product/normal 的 NormalOrderCancelTask 承接
            // （normal/App.php 注册 mark=orderCancel，与本定时任务种子对齐；run() 分发时 registry 优先命中）
            $this->crontabLog(' 执行未支付自动取消订单');
        } catch (\Throwable $e) {
            $this->crontabLog('自动取消订单失败,失败原因:' . $e->getMessage());
        }
    }

    public function takeDelivery()
    {
        try {
            app()->make(OrderTakeServices::class)->autoTakeOrder();
            $this->crontabLog(' 执行自动收货');
        } catch (\Throwable $e) {
            $this->crontabLog('自动收货失败,失败原因:' . $e->getMessage());
        }
    }

    public function productReplay()
    {
        try {
            // 自动好评走订单中台：待评价订单收货超时后按商品生成五星好评
            app()->make(OrderCenterServices::class)->autoComment();
            $this->crontabLog(' 执行自动好评');
        } catch (\Throwable $e) {
            $this->crontabLog('自动好评失败,失败原因:' . $e->getMessage());
        }
    }

    public function clearPoster()
    {
        try {
            app()->make(SystemAttachmentServices::class)->emptyYesterdayAttachment();
            $this->crontabLog(' 执行清除昨日海报');
        } catch (\Throwable $e) {
            $this->crontabLog('清除昨日海报失败,失败原因:' . $e->getMessage());
        }
    }

    public function autoInvoice()
    {
        try {
            $invoiceServices = app()->make(OrderInvoiceServices::class);
            $invoiceServices->autoInvoice();
            $invoiceServices->autoInvoiceRed();
            $this->crontabLog(' 执行自动开具/冲红电子发票');
        } catch (\Throwable $e) {
            $this->crontabLog('自动开具/冲红电子发票失败,失败原因:' . $e->getMessage());
        }
    }

    public function refundRecover()
    {
        try {
            $result = app()->make(RefundOrderServices::class)->recover(100);
            $this->crontabLog(' 退款一致性恢复结果：' . json_encode($result, JSON_UNESCAPED_UNICODE));
            if ($result['failed'] > 0 || $result['manual_review'] > 0) {
                throw new \RuntimeException('退款恢复存在失败或需人工复核的记录：'
                    . json_encode($result, JSON_UNESCAPED_UNICODE) . '，请检查退款单 last_error');
            }
        } catch (\Throwable $e) {
            $this->crontabLog('退款一致性恢复失败,失败原因:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 每分钟分批恢复微信支付任务。
     * @return void
     */
    public function paymentRecover(): void
    {
        $result = app()->make(PayRecordServices::class)->recover();
        $this->crontabLog('微信支付恢复结果：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['failed']) throw new \RuntimeException('微信支付恢复存在失败任务，请检查脱敏支付日志');
    }

    public function customTimer($customCode = '')
    {
        try {
            eval($customCode);
            $this->crontabLog(' 自定义定时器执行成功');
        } catch (\Throwable $e) {
            $this->crontabLog('自定义定时器执行失败,失败原因:' . $e->getMessage());
        }
    }

    public function noticeUnpaidOrder()
    {
        try {
            // 扫描超时未支付订单并入队提醒（下单 10 分钟的即时提醒由队列路径承担，本任务负责后续补提醒）
            $count = app()->make(OrderCenterServices::class)->scanUnpaidOrders();
            $this->crontabLog(' 执行未支付订单提醒，本次入队 ' . $count . ' 单');
        } catch (\Throwable $e) {
            $this->crontabLog('未支付订单提醒失败,失败原因:' . $e->getMessage());
        }
    }

    public function noticeMemberExpire()
    {
        try {
            // 会员到期提醒：暂无监听器处理

            $this->crontabLog(' 执行会员到期提醒');
        } catch (\Throwable $e) {
            $this->crontabLog('会员到期提醒失败,失败原因:' . $e->getMessage());
        }
    }
}
