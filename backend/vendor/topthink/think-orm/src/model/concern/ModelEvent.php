<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\model\concern;

use ReflectionClass;
use think\db\exception\ModelEventException;
use think\helper\Str;

/**
 * 模型事件处理.
 */
trait ModelEvent
{
    /**
     * 设置Event对象 （用于兼容）
     *
     * @param object $event Event对象
     *
     * @return void
     */
    public static function setEvent($event)
    {}

    /**
     * 当前操作的事件响应.
     *
     * @param bool $event 是否需要事件响应
     *
     * @return $this
     */
    public function withEvent(bool $event)
    {
        return $this->setOption('withEvent', $event);
    }

    /**
     * 触发事件.
     *
     * @param string $event 事件名
     *
     * @return bool
     */
    protected function trigger(string $event): bool
    {
        if (!$this->getOption('withEvent', true)) {
            return true;
        }

        $method = 'on' . Str::studly($event);
        $obj    = $this->getOption('event');
        $obser  = $this->getOption('eventObserver');
        try {
            if ($obser) {
                $reflect  = new ReflectionClass($obser);
                $observer = $reflect->newinstance();
            } else {
                $observer = $this;
            }

            if (method_exists($observer, $method)) {
                $result = $this->invoke([$observer, $method], [$this]);
            } elseif (is_object($obj) && method_exists($obj, 'trigger')) {
                $result = $obj->trigger(static::class . '.' . $event, $this);
                $result = empty($result) ? true : end($result);
            } else {
                $result = true;
            }

            return false !== $result;
        } catch (ModelEventException $e) {
            return false;
        }
    }
}
