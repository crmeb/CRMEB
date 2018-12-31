<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

\think\Console::addDefaultCommands([
    "think\\migration\\command\\migrate\\Create",
    "think\\migration\\command\\migrate\\Run",
    "think\\migration\\command\\migrate\\Rollback",
    "think\\migration\\command\\migrate\\Breakpoint",
    "think\\migration\\command\\migrate\\Status",
    "think\\migration\\command\\seed\\Create",
    "think\\migration\\command\\seed\\Run",
]);
