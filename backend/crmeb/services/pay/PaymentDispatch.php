<?php
declare(strict_types=1);

namespace crmeb\services\pay;

/**
 * 支付事务内的后置任务收集器，持久化和重放由应用层负责。
 */
class PaymentDispatch
{
    /** @var array|null 当前事务收集的任务 */
    protected static ?array $tasks = null;

    /** @var bool 是否正在重放可靠任务 */
    protected static bool $replaying = false;

    /**
     * 重放时让旁路异常上抛，保留失败任务供下次恢复。
     * @param callable $callback 后置任务
     * @return mixed
     */
    public static function replay(callable $callback)
    {
        $previous = self::$replaying;
        self::$replaying = true;
        try {
            return $callback();
        } finally {
            self::$replaying = $previous;
        }
    }

    /**
     * 是否正在重放可靠任务。
     * @return bool
     */
    public static function replaying(): bool
    {
        return self::$replaying;
    }

    /**
     * 收集任务，失败时丢弃未提交的任务。
     * @param callable $callback 事务执行器
     * @return mixed
     */
    public static function collect(callable $callback)
    {
        if (self::$tasks !== null) return $callback();
        self::$tasks = [];
        try {
            return $callback();
        } finally {
            self::$tasks = null;
        }
    }

    /**
     * 在支付事务内延后队列或旁路事件。
     * @param array $task 可序列化任务
     * @return bool 是否已收集
     */
    public static function defer(array $task): bool
    {
        if (self::$tasks === null) return false;
        if (($task['type'] ?? '') === 'job' && !empty($task['secs'])) $task['available_at'] = time() + (int)$task['secs'];
        self::$tasks[] = $task;
        return true;
    }

    /**
     * 获取当前事务待持久化的任务。
     * @return array
     */
    public static function tasks(): array
    {
        return self::$tasks ?? [];
    }

    /**
     * 是否位于已收集后置任务的支付事务中。
     * @return bool
     */
    public static function collecting(): bool
    {
        return self::$tasks !== null;
    }
}
