<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\console\command\make;

use think\console\command\Make;

class Service extends Make
{
    protected $type = "Service";

    protected function configure()
    {
        parent::configure();
        $this->setName('make:service')
            ->setDescription('Create a new Service class');
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'service.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\service';
    }
}
