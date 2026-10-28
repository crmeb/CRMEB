<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace crmeb\utils;

use think\Event;
use think\facade\Log;
use think\helper\Str;

/**
 * 安全事件调度器
 *
 * 继承框架事件管理器，在逐个调度监听器时增加异常隔离与事件分级，解决
 * "一个插件监听器抛异常导致下单/支付主流程中断"的稳定性问题：
 *
 * - 事件分级：旁路型事件（展示扩展/通知推送/营销旁路/统计记录/渠道同步/定时任务收尾钩子）
 *   列入安全事件清单，其下所有监听器异常只记日志、不中断主流程；
 * - 监听器分级：任何事件下，通知类（*NoticeListener）与展示扩展类（*ExtendListener）
 *   后缀的监听器异常一律隔离，避免"业务监听器成功但通知失败拖垮主流程"；
 * - 关键链路保持原语义：订单/资金/库存等数据一致性事件中的关键监听器异常仍向上抛出，
 *   确保订单状态、库存、资金流水不会被静默吞掉而出现数据不一致。
 *
 * 通过 app/provider.php 将容器标识 think\Event 重绑到本类生效，event()/Event::trigger()
 * 等既有调用方式无需任何改动；本类 instanceof think\Event，对框架与第三方类型约束完全兼容。
 * @package crmeb\utils
 * @see \app\provider.php 容器绑定入口
 */
class SafeEvent extends Event
{
    /**
     * 安全事件清单（旁路型事件：其下所有监听器异常仅记日志，不中断主流程）
     * 关键事件（订单/资金/库存等数据一致性事件、服务代理与查询类）不在此列，保持异常上抛
     * @var array|string[]
     */
    protected array $safeEvents = [
        // 展示扩展类：失败降级为"区块缺失"，不影响接口主数据
        'OrderCenterListExtend',
        'OrderCenterDetailExtend',
        'ProductDetailExtend',
        'AfterSaleListSpecExtend',
        'UserListExtend',
        'UserDetailExtend',
        'UserVisitExtend',
        // 通知/推送类独立事件
        'UnpaidOrderNotice',
        'OrderPaySuccessCustomerNotice',
        'OrderShipping',
        'OutPush',
        'UserRecharge',
        'UserRechargeRefund',
        'UserRevenue',
        'UserBalanceChange',
        // 营销旁路类：失败影响活动参与但不应影响交易主流程
        'OrderPayLottery',
        'OrderCommentLotteryCheck',
        'Sign',
        'Lottery',
        // 统计/记录类
        'UserVisit',
        'HttpEnd',
        // 渠道同步类
        'WechatFanSync',
        // 插件扩展点：MCP 工具注册，单个插件监听器异常只影响其工具可用性，降级不中断服务
        'McpToolsRegister',
        // 定时任务收尾钩子（CrontabBeforeRun 为执行前置校验，保持关键级）
        'CrontabAfterRun',
        'CrontabSuccess',
        'CrontabFail',
    ];

    /**
     * 安全监听器类名后缀：任何事件下，此类监听器异常一律隔离（记日志后继续执行后续监听器）
     * 通知类监听器与展示扩展类监听器均属旁路职责，不应阻断业务主流程
     * @var array|string[]
     */
    protected array $safeListenerSuffixes = ['NoticeListener', 'ExtendListener'];

    /**
     * 触发事件（在框架调度逻辑基础上增加监听器异常隔离与事件分级）
     * @param string|object $event  事件名称或事件对象
     * @param mixed         $params 传入参数
     * @param bool          $once   只获取一个有效返回值
     * @return mixed
     */
    public function trigger($event, $params = null, bool $once = false)
    {
        if (is_object($event)) {
            $params = $event;
            $event  = $event::class;
        }

        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }

        // 事件分级判定：安全事件下所有监听器异常均隔离
        $isSafeEvent = in_array($event, $this->safeEvents, true);
        if ($isSafeEvent && \crmeb\services\pay\PaymentDispatch::defer(['type' => 'event', 'event' => $event, 'params' => $params])) {
            return $once ? true : [];
        }

        $result    = [];
        $listeners = $this->listener[$event] ?? [];

        if (str_contains($event, '.')) {
            [$prefix, $name] = explode('.', $event, 2);
            if (isset($this->observer[$prefix])) {
                // 检查观察者事件响应方法
                $observer = $this->observer[$prefix];
                $method   = 'on' . Str::studly($name);
                if (method_exists($observer, $method)) {
                    return $this->dispatch([$observer, $method], $params);
                }
            }

            $name = substr($event, 0, strrpos($event, '.'));
            if (isset($this->listener[$name . '.*'])) {
                $listeners = array_merge($listeners, $this->listener[$name . '.*']);
            }
        }

        $listeners = array_unique($listeners, SORT_REGULAR);

        foreach ($listeners as $key => $listener) {
            if (is_string($listener) && $this->isSafeListener($listener)
                && \crmeb\services\pay\PaymentDispatch::defer(['type' => 'listener', 'class' => $listener, 'params' => $params])) {
                continue;
            }
            try {
                $result[$key] = $this->dispatch($listener, $params);
            } catch (\Throwable $e) {
                // 关键事件中的关键监听器：异常继续上抛中断主流程，保证订单/资金/库存数据一致性
                if (\crmeb\services\pay\PaymentDispatch::replaying() || (!$isSafeEvent && !$this->isSafeListener($listener))) {
                    throw $e;
                }
                // 安全监听器异常：记录日志后继续执行后续监听器，不中断主流程
                $this->logListenerException($event, $listener, $e);
                continue;
            }

            if (false === $result[$key] || (!is_null($result[$key]) && $once)) {
                if (false === $result[$key] && (\crmeb\services\pay\PaymentDispatch::replaying()
                    || (\crmeb\services\pay\PaymentDispatch::collecting() && !$isSafeEvent))) {
                    throw new \RuntimeException('支付后置事件处理失败：' . $event);
                }
                break;
            }
        }

        return $once ? end($result) : $result;
    }

    /**
     * 判断监听器是否为安全监听器（通知类/展示扩展类后缀）
     * @param mixed $listener 监听器定义（类名字符串、Class::method 静态调用、[对象/类名, 方法] 数组、闭包）
     * @return bool 闭包或无法识别类名时视为关键监听器
     */
    protected function isSafeListener($listener): bool
    {
        $class = $this->parseListenerClass($listener);
        if ($class === '') {
            return false;
        }

        foreach ($this->safeListenerSuffixes as $suffix) {
            if (str_ends_with($class, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 从监听器定义中解析类名（兼容类名字符串、Class::method 静态调用、[对象/类名, 方法] 数组）
     * @param mixed $listener 监听器定义
     * @return string 类名（闭包或无法识别时返回空字符串）
     */
    protected function parseListenerClass($listener): string
    {
        if (is_object($listener)) {
            return $listener::class;
        }

        if (is_array($listener)) {
            $target = $listener[0] ?? null;
            return is_object($target) ? $target::class : (string) $target;
        }

        if (is_string($listener)) {
            return str_contains($listener, '::') ? strstr($listener, '::', true) : $listener;
        }

        return '';
    }

    /**
     * 记录被隔离的监听器异常（含事件名、监听器类与异常定位，便于线上排障复盘）
     * @param string     $event    事件名
     * @param mixed      $listener 监听器定义
     * @param \Throwable $e        异常对象
     * @return void
     */
    protected function logListenerException(string $event, $listener, \Throwable $e): void
    {
        $class = $this->parseListenerClass($listener) ?: '(closure)';
        Log::error(sprintf(
            '事件监听器异常已隔离 event=%s listener=%s error=%s in %s:%d',
            $event,
            $class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
    }
}
