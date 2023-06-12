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

namespace think\facade;

use think\Facade;

/**
 * @see \think\App
 * @package think\facade
 * @mixin \think\App
 * @method static \think\Service|null register(\think\Service|string $service, bool $force = false) 注册服务
 * @method static mixed bootService(\think\Service $service) 执行服务
 * @method static \think\Service|null getService(string|\think\Service $service) 获取服务
 * @method static \think\App debug(bool $debug = true) 开启应用调试模式
 * @method static bool isDebug() 是否为调试模式
 * @method static \think\App setNamespace(string $namespace) 设置应用命名空间
 * @method static string getNamespace() 获取应用类库命名空间
 * @method static string version() 获取框架版本
 * @method static string getRootPath() 获取应用根目录
 * @method static string getBasePath() 获取应用基础目录
 * @method static string getAppPath() 获取当前应用目录
 * @method static mixed setAppPath(string $path) 设置应用目录
 * @method static string getRuntimePath() 获取应用运行时目录
 * @method static void setRuntimePath(string $path) 设置runtime目录
 * @method static string getThinkPath() 获取核心框架目录
 * @method static string getConfigPath() 获取应用配置目录
 * @method static string getConfigExt() 获取配置后缀
 * @method static float getBeginTime() 获取应用开启时间
 * @method static integer getBeginMem() 获取应用初始内存占用
 * @method static \think\App initialize() 初始化应用
 * @method static bool initialized() 是否初始化过
 * @method static void loadLangPack(string $langset) 加载语言包
 * @method static void boot() 引导应用
 * @method static void loadEvent(array $event) 注册应用事件
 * @method static string parseClass(string $layer, string $name) 解析应用类的类名
 * @method static bool runningInConsole() 是否运行在命令行下
 */
class App extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'app';
    }
}
