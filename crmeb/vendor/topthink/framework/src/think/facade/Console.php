<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\facade;

use think\console\Command;
use think\console\Input;
use think\console\input\Definition as InputDefinition;
use think\console\Output;
use think\console\output\driver\Buffer;
use think\Facade;

/**
 * Class Console
 * @package think\facade
 * @mixin \think\Console
 * @method static Output|Buffer call(string $command, array $parameters = [], string $driver = 'buffer')
 * @method static int run() 执行当前的指令
 * @method static int doRun(Input $input, Output $output) 执行指令
 * @method static void setDefinition(InputDefinition $definition) 设置输入参数定义
 * @method static InputDefinition The InputDefinition instance getDefinition() 获取输入参数定义
 * @method static string A help message. getHelp() Gets the help message.
 * @method static void setCatchExceptions(bool $boolean) 是否捕获异常
 * @method static void setAutoExit(bool $boolean) 是否自动退出
 * @method static string getLongVersion() 获取完整的版本号
 * @method static void addCommands(array $commands) 添加指令集
 * @method static Command|void addCommand(string|Command $command, string $name = '') 添加一个指令
 * @method static Command getCommand(string $name) 获取指令
 * @method static bool hasCommand(string $name) 某个指令是否存在
 * @method static array getNamespaces() 获取所有的命名空间
 * @method static string findNamespace(string $namespace) 查找注册命名空间中的名称或缩写。
 * @method static Command find(string $name) 查找指令
 * @method static Command[] all(string $namespace = null) 获取所有的指令
 * @method static string extractNamespace(string $name, int $limit = 0) 返回命名空间部分
 */
class Console extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'console';
    }
}
