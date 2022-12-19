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

namespace crmeb\traits;


use crmeb\utils\Queue;

/**
 * 快捷加入消息队列
 * Trait QueueTrait
 * @package crmeb\traits
 */
trait QueueTrait
{
    /**
     * 列名
     * @return string
     */
    protected static function queueName()
    {
        return null;
    }

    /**
     * 加入队列
     * @param array|string|int $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatch($action, array $data = [], string $queueName = null)
    {
        $queue = Queue::instance()->job(__CLASS__);
        if (is_array($action)) {
            $queue->data(...$action);
        } else if (is_string($action)) {
            $queue->do($action)->data(...$data);
        }
        if ($queueName) {
            $queue->setQueueName($queueName);
        } else if (static::queueName()) {
            $queue->setQueueName(static::queueName());
        }
        return $queue->push();
    }

    /**
     * 延迟加入消息队列
     * @param int $secs
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatchSece(int $secs, $action, array $data = [], string $queueName = null)
    {
        $queue = Queue::instance()->job(__CLASS__)->secs($secs);
        if (is_array($action)) {
            $queue->data(...$action);
        } else if (is_string($action)) {
            $queue->do($action)->data(...$data);
        }
        if ($queueName) {
            $queue->setQueueName($queueName);
        } else if (static::queueName()) {
            $queue->setQueueName(static::queueName());
        }
        return $queue->push();
    }

    /**
     * 加入小队列
     * @param string $do
     * @param array $data
     * @param int|null $secs
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatchDo(string $do, array $data = [], int $secs = null, string $queueName = null)
    {
        $queue = Queue::instance()->job(__CLASS__)->do($do);
        if ($secs) {
            $queue->secs($secs);
        }
        if ($data) {
            $queue->data(...$data);
        }
        if ($queueName) {
            $queue->setQueueName($queueName);
        } else if (static::queueName()) {
            $queue->setQueueName(static::queueName());
        }
        return $queue->push();
    }

}
