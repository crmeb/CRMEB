<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/9 16:50
 */

namespace crmeb\traits;

use crmeb\exceptions\AuthException;

trait LogicTrait
{

    /**
     * @var array
     */
    protected $items = [];

    /**
     * 实例化本身
     * @var object
     */
    protected static $instance;

    /**
     * 配置参数
     * @param array $config
     */
    protected function setConfig(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->set($this->items, $key, $value);
        }
    }

    /**
     * 设置参数
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * 实例化类
     */
    protected function registerProviders()
    {
        if (property_exists($this, 'providers')) {
            foreach ($this->providers as $key => $provider) {
                $this->register(new $provider(), $key);
            }
        }
    }

    /**
     * 获取类内配置信息
     * @param object $pimple
     * @return $this
     * */
    protected function register($pimple, $key)
    {
        $response = $pimple->register($this->items);
        if (is_array($response)) {
            [$key, $provider] = $response;
            $this->$key = $provider;
        } else if (is_string($key)) {
            $this->$key = $pimple;
        }
        return $this;
    }

    /**
     * 实例化本类
     * @param array $config
     * @return $this
     * */
    public static function instance($config = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->setConfig($config);
            self::$instance->registerProviders();
            if (method_exists(self::$instance, 'bool'))
                self::$instance->bool();
        }
        return self::$instance;
    }

    /**
     * 转数据类型
     * @param $var
     * @param string $type
     * @return array|bool|float|int|string|null
     */
    public function toType($var, $type = 'string')
    {
        if ($type === 'string') {
            return (string)$var;
        } else if ($type === 'array') {
            return is_array($var) ? $var : [$var];
        } else if ($type === 'boolean') {
            return (bool)$var;
        } else if ($type === 'float') {
            return (float)$var;
        } else if ($type === 'int') {
            return (int)$var;
        } else if ($type === 'null') {
            return null;
        }
        return $var;
    }

    /**
     * 设置属性
     * @param $method
     * @param $ages
     * @return $this
     */
    public function __call($method, $ages)
    {
        $keys = property_exists($this, 'providers') ? array_keys($this->providers) : [];
        $propsRuleKeys = property_exists($this, 'propsRule') ? array_keys($this->propsRule) : [];
        if (strstr($method, 'set') !== false) {
            $attribute = lcfirst(str_replace('set', '', $method));
            if (property_exists($this, $attribute) && in_array($attribute, $propsRuleKeys)) {
                $propsRuleValeu = $this->propsRule[$attribute];
                $valeu = null;
                if (is_array($propsRuleValeu)) {
                    $type = $propsRuleValeu[1] ?? 'string';
                    $callable = $propsRuleValeu[2] ?? null;
                    if ($type == 'callable' && $callable) {
                        $callable = $propsRuleValeu[2];
                        if (method_exists($this, $callable))
                            $ages[0] = $this->{$callable}($ages[0], $ages[1] ?? '');

                    } else if ($type) {
                        $ages[0] = $this->toType($ages[0], $type) ?? null;
                    }
                } else {
                    $valeu = $propsRuleValeu;
                }

                $this->{$attribute} = $ages[0] ?? $valeu;
                return $this;
            }
        } else if (in_array($method, $keys)) {
            if (property_exists($this, 'handleType') && array_shift($ages) !== true) {
                $this->handleType = $method;
                return $this;
            } else {
                return $this->{$method};
            }
        } else {
            throw new AuthException('Method does not exist：' . $method);
        }
    }
}