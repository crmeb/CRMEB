<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\model\concern;

use think\Container;
use think\exception\ModelEventException;

/**
 * 模型事件处理
 */
trait ModelEvent
{

    /**
     * 是否需要事件响应
     * @var bool
     */
    protected $withEvent = true;

    /**
     * 当前操作的事件响应
     * @access protected
     * @param  bool $event  是否需要事件响应
     * @return $this
     */
    public function withEvent(bool $event)
    {
        $this->withEvent = $event;
        return $this;
    }

    /**
     * 触发事件
     * @access protected
     * @param  string $event 事件名
     * @return bool
     */
    protected function trigger(string $event): bool
    {
        if (!$this->withEvent) {
            return true;
        }

        $call = 'on' . Container::parseName($event, 1);

        try {
            if (method_exists(static::class, $call)) {
                $result = Container::getInstance()
                    ->invoke([static::class, $call], [$this]);
            } else {
                $result = true;
            }

            return false === $result ? false : true;
        } catch (ModelEventException $e) {
            return false;
        }
    }
}
