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

namespace think;

/**
 * 数据库管理类
 * @package think
 */
class Db extends DbManager
{
    /**
     * @param Event  $event
     * @param Config $config
     * @param Log    $log
     * @param Cache  $cache
     * @return Db
     * @codeCoverageIgnore
     */
    public static function __make(Event $event, Config $config, Log $log, Cache $cache)
    {
        $db = new static();
        $db->init($config->get('database'));
        $db->setEvent($event);
        $db->setLog($log);
        $db->setCache($cache);

        return $db;
    }

    /**
     * 设置Event对象
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * 设置日志对象
     * @param Log $log
     * @return void
     */
    public function setLog(Log $log)
    {
        $this->log = $log;
    }

    /**
     * 记录SQL日志
     * @access protected
     * @param string $log  SQL日志信息
     * @param string $type 日志类型
     * @return void
     */
    public function log($log, $type = 'sql')
    {
        $this->log->record($log, $type);
    }

    /**
     * 注册回调方法
     * @access public
     * @param string   $event    事件名
     * @param callable $callback 回调方法
     * @return void
     */
    public function event(string $event, callable $callback): void
    {
        if ($this->event) {
            $this->event->listen('db.' . $event, $callback);
        }
    }

    /**
     * 触发事件
     * @access public
     * @param string $event  事件名
     * @param mixed  $params 传入参数
     * @param bool   $once
     * @return mixed
     */
    public function trigger(string $event, $params = null, bool $once = false)
    {
        if ($this->event) {
            return $this->event->trigger('db.' . $event, $params, $once);
        }
    }
}
