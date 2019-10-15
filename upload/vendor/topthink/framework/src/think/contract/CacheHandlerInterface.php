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

namespace think\contract;

/**
 * 缓存驱动接口
 */
interface CacheHandlerInterface
{
    /**
     * 判断缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool;

    /**
     * 读取缓存
     * @access public
     * @param  string $name 缓存变量名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false);

    /**
     * 写入缓存
     * @access public
     * @param  string            $name 缓存变量名
     * @param  mixed             $value  存储数据
     * @param  integer|\DateTime $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null): bool;

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int    $step 步长
     * @return false|int
     */
    public function inc(string $name, int $step = 1);

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int    $step 步长
     * @return false|int
     */
    public function dec(string $name, int $step = 1);

    /**
     * 删除缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public function delete($name): bool;

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear(): bool;

}
