<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\console\command;

use think\console\Command;
use think\console\Input;

/**
 * @mixin Command
 */
trait CommandCallable
{
    /**
     * @param class-string<Command> $class
     */
    private function callCommand(string $class): Command
    {
        return tap(app($class, newInstance: true), function ($command) {
            $command->setApp($this->app);
            $command->run(new Input([]), clone $this->output);
        });
    }
}
