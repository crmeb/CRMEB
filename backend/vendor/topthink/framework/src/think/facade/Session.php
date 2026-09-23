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
declare(strict_types=1);

namespace think\facade;

use think\Facade;

/**
 * @see \think\Session
 * @package think\facade
 * @mixin \think\Session
 * @method static void set(string $name, mixed $value) 设置Session值
 * @method static mixed get(string $name, mixed $default = null) 获取Session值
 * @method static array all() 获取全部Session值
 * @method static void delete(string $name) 删除Session值
 * @method static mixed pull(string $name) 获取并删除Session值
 * @method static void push(string $key, mixed $value) 添加数据到一个session数组
 * @method static bool has(string $name) 判断Session值
 * @method static void clear() 清空Session数据
 * @method static void destroy() 销毁Session
 * @method static void save() 写入Session数据（通常情况自动写入）
 * @method static mixed getConfig(null|string $name = null, mixed $default = null) 获取Session配置
 * @method static string|null getDefaultDriver() 默认驱动
 */
class Session extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'session';
    }
}
