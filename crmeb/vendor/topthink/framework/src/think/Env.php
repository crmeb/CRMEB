<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use ArrayAccess;

/**
 * Env管理类
 * @package think
 */
class Env implements ArrayAccess
{
    /**
     * 环境变量数据
     * @var array
     */
    protected $data = [];

    /**
     * 数据转换映射
     * @var array
     */
    protected $convert = [
        'true'  => true,
        'false' => false,
        'off'   => false,
        'on'    => true,
    ];

    public function __construct()
    {
        $this->data = $_ENV;
    }

    /**
     * 读取环境变量定义文件
     * @access public
     * @param string $file 环境变量定义文件
     * @return void
     */
    public function load(string $file): void
    {
        $env = parse_ini_file($file, true, INI_SCANNER_RAW) ?: [];
        $this->set($env);
    }

    /**
     * 获取环境变量值
     * @access public
     * @param string $name    环境变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get(string $name = null, $default = null)
    {
        if (is_null($name)) {
            return $this->data;
        }

        $name = strtoupper(str_replace('.', '_', $name));
        if (isset($this->data[$name])) {
            $result = $this->data[$name];

            if (is_string($result) && isset($this->convert[$result])) {
                return $this->convert[$result];
            }

            return $result;
        }

        return $this->getEnv($name, $default);
    }

    protected function getEnv(string $name, $default = null)
    {
        $result = getenv('PHP_' . $name);

        if (false === $result) {
            return $default;
        }

        if ('false' === $result) {
            $result = false;
        } elseif ('true' === $result) {
            $result = true;
        }

        if (!isset($this->data[$name])) {
            $this->data[$name] = $result;
        }

        return $result;
    }

    /**
     * 设置环境变量值
     * @access public
     * @param string|array $env   环境变量
     * @param mixed        $value 值
     * @return void
     */
    public function set($env, $value = null): void
    {
        if (is_array($env)) {
            $env = array_change_key_case($env, CASE_UPPER);

            foreach ($env as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $this->data[$key . '_' . strtoupper($k)] = $v;
                    }
                } else {
                    $this->data[$key] = $val;
                }
            }
        } else {
            $name = strtoupper(str_replace('.', '_', $env));

            $this->data[$name] = $value;
        }
    }

    /**
     * 检测是否存在环境变量
     * @access public
     * @param string $name 参数名
     * @return bool
     */
    public function has(string $name): bool
    {
        return !is_null($this->get($name));
    }

    /**
     * 设置环境变量
     * @access public
     * @param string $name  参数名
     * @param mixed  $value 值
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * 获取环境变量
     * @access public
     * @param string $name 参数名
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 检测是否存在环境变量
     * @access public
     * @param string $name 参数名
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    // ArrayAccess
    #[\ReturnTypeWillChange]
    public function offsetSet($name, $value): void
    {
        $this->set($name, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($name): bool
    {
        return $this->__isset($name);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($name): void
    {
        throw new Exception('not support: unset');
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return $this->get($name);
    }
}
