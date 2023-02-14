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
     * @return null
     */
    protected static function queueName()
    {
        return null;
    }

    /**
     * 加入队列
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatch($action, array $data = [], string $queueName = null)
    {
        if (sys_config('queue_open', 0) == 1) {
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
        } else {
            $className = '\\' . __CLASS__;
            $res = new $className();
            if (is_array($action)) {
                $res->doJob(...$action);
            } else {
                $res->$action(...$data);
            }

        }
    }

    /**
     * 延迟加入消息队列
     * @param int $secs
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatchSecs(int $secs, $action, array $data = [], string $queueName = null)
    {
        if (sys_config('queue_open', 0) == 1) {
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
    }
}
