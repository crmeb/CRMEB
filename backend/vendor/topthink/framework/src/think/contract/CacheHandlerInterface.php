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
declare(strict_types = 1);

namespace think\contract;

use DateInterval;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;
use think\cache\TagSet;

/**
 * 缓存驱动接口
 */
interface CacheHandlerInterface extends CacheInterface
{

    /**
     * 自增缓存（针对数值缓存）
     * @param string $name 缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1);

    /**
     * 自减缓存（针对数值缓存）
     * @param string $name 缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1);

    /**
     * 读取缓存并删除
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function pull($name);

    /**
     * 如果不存在则写入缓存
     * @param string                             $name   缓存变量名
     * @param mixed                              $value  存储数据
     * @param int|DateInterval|DateTimeInterface $expire 有效时间 0为永久
     * @return mixed
     */
    public function remember($name, $value, $expire = null);

    /**
     * 缓存标签
     * @param string|array $name 标签名
     * @return TagSet
     */
    public function tag($name);

    /**
     * 删除缓存标签
     * @param array $keys 缓存标识列表
     * @return void
     */
    public function clearTag($keys);
}
