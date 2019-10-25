<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Slince <taosikai@yeah.net>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\console\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class RunServer extends Command
{
    public function configure()
    {
        $this->setName('run')
            ->addOption('host', 'H', Option::VALUE_OPTIONAL,
                'The host to server the application on', '0.0.0.0')
            ->addOption('port', 'p', Option::VALUE_OPTIONAL,
                'The port to server the application on', 8000)
            ->addOption('root', 'r', Option::VALUE_OPTIONAL,
                'The document root of the application', '')
            ->setDescription('PHP Built-in Server for ThinkPHP');
    }

    public function execute(Input $input, Output $output)
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $root = $input->getOption('root');
        if (empty($root)) {
            $root = $this->app->getRootPath() . 'public';
        }

        $command = sprintf(
            'php -S %s:%d -t %s %s',
            $host,
            $port,
            escapeshellarg($root),
            escapeshellarg($root . DIRECTORY_SEPARATOR . 'router.php')
        );

        $output->writeln(sprintf('ThinkPHP Development server is started On <http://%s:%s/>', '0.0.0.0' == $host ? '127.0.0.1' : $host, $port));
        $output->writeln(sprintf('You can exit with <info>`CTRL-C`</info>'));
        $output->writeln(sprintf('Document root is: %s', $root));
        passthru($command);
    }

}
