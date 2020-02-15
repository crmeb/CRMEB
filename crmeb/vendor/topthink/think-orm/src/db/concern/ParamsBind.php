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

namespace think\db\concern;

use PDO;

/**
 * 参数绑定支持
 */
trait ParamsBind
{
    /**
     * 当前参数绑定
     * @var array
     */
    protected $bind = [];

    /**
     * 批量参数绑定
     * @access public
     * @param array $value 绑定变量值
     * @return $this
     */
    public function bind(array $value)
    {
        $this->bind = array_merge($this->bind, $value);
        return $this;
    }

    /**
     * 单个参数绑定
     * @access public
     * @param mixed   $value 绑定变量值
     * @param integer $type  绑定类型
     * @param string  $name  绑定标识
     * @return string
     */
    public function bindValue($value, int $type = null, string $name = null)
    {
        $name = $name ?: 'ThinkBind_' . (count($this->bind) + 1) . '_' . mt_rand() . '_';

        $this->bind[$name] = [$value, $type ?: PDO::PARAM_STR];
        return $name;
    }

    /**
     * 检测参数是否已经绑定
     * @access public
     * @param string $key 参数名
     * @return bool
     */
    public function isBind($key)
    {
        return isset($this->bind[$key]);
    }

    /**
     * 参数绑定
     * @access public
     * @param string $sql  绑定的sql表达式
     * @param array  $bind 参数绑定
     * @return void
     */
    protected function bindParams(string &$sql, array $bind = []): void
    {
        foreach ($bind as $key => $value) {
            if (is_array($value)) {
                $name = $this->bindValue($value[0], $value[1], $value[2] ?? null);
            } else {
                $name = $this->bindValue($value);
            }

            if (is_numeric($key)) {
                $sql = substr_replace($sql, ':' . $name, strpos($sql, '?'), 1);
            } else {
                $sql = str_replace(':' . $key, ':' . $name, $sql);
            }
        }
    }

    /**
     * 获取绑定的参数 并清空
     * @access public
     * @param bool $clear 是否清空绑定数据
     * @return array
     */
    public function getBind(bool $clear = true): array
    {
        $bind = $this->bind;
        if ($clear) {
            $this->bind = [];
        }

        return $bind;
    }
}
