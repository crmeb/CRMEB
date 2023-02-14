<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Workerman\Crontab;
use Workerman\Lib\Timer;

/**
 * Class Crontab
 * @package Workerman\Crontab
 */
class Crontab
{
    /**
     * @var string
     */
    protected $_rule;

    /**
     * @var callable
     */
    protected $_callback;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var int
     */
    protected $_id;

    /**
     * @var array
     */
    protected static $_instances = [];

    /**
     * Crontab constructor.
     * @param $rule
     * @param $callback
     * @param null $name
     */
    public function __construct($rule, $callback, $name = null)
    {
        $this->_rule = $rule;
        $this->_callback = $callback;
        $this->_name = $name;
        $this->_id = static::createId();
        static::$_instances[$this->_id] = $this;
        static::tryInit();
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->_rule;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        return static::remove($this->_id);
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return static::$_instances;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function remove($id)
    {
        if ($id instanceof Crontab) {
            $id = $id->getId();
        }
        if (!isset(static::$_instances[$id])) {
            return false;
        }
        unset(static::$_instances[$id]);
        return true;
    }

    /**
     * @return int
     */
    protected static function createId()
    {
        static $id = 0;
        return ++$id;
    }

    /**
     * tryInit
     */
    protected static function tryInit()
    {
        static $inited = false;
        if ($inited) {
            return;
        }
        $inited = true;
        $parser = new Parser();
        $callback = function () use ($parser, &$callback) {
            foreach (static::$_instances as $crontab) {
                $rule = $crontab->getRule();
                $cb = $crontab->getCallback();
                if (!$cb || !$rule) {
                    continue;
                }
                $times = $parser->parse($rule);
                $now = time();
                foreach ($times as $time) {
                    $t = $time-$now;
                    if ($t <= 0) {
                        $t = 0.000001;
                    }
                    Timer::add($t, $cb, null, false);
                }
            }
            Timer::add(60 - time()%60, $callback, null, false);
        };

        $next_time = time()%60;
        if ($next_time == 0) {
            $next_time = 0.00001;
        } else {
            $next_time = 60 - $next_time;
        }
        Timer::add($next_time, $callback, null, false);
    }

}