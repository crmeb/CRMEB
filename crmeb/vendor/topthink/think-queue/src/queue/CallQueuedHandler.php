<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\queue;

class CallQueuedHandler
{

    public function call(Job $job, array $data)
    {
        $command = unserialize($data['command']);

        call_user_func([$command, 'handle']);

        if (!$job->isDeletedOrReleased()) {
            $job->delete();
        }
    }

    public function failed(array $data)
    {
        $command = unserialize($data['command']);

        if (method_exists($command, 'failed')) {
            $command->failed();
        }
    }
}
