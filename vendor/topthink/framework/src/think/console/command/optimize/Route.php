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
namespace think\console\command\optimize;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\event\RouteLoaded;

class Route extends Command
{
    protected function configure()
    {
        $this->setName('optimize:route')
            ->addArgument('app', Argument::OPTIONAL, 'app name.')
            ->setDescription('Build app route cache.');
    }

    protected function execute(Input $input, Output $output)
    {
        $app = $input->getArgument('app');

        if (empty($app) && !is_dir($this->app->getBasePath() . 'controller')) {
            $output->writeln('<error>Miss app name!</error>');
            return false;
        }

        $path = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . ($app ? $app . DIRECTORY_SEPARATOR : '');

        $filename = $path . 'route.php';
        if (is_file($filename)) {
            unlink($filename);
        }

        file_put_contents($filename, $this->buildRouteCache($app));
        $output->writeln('<info>Succeed!</info>');
    }

    protected function buildRouteCache(string $app = null): string
    {
        $this->app->route->clear();
        $this->app->route->lazy(false);

        // 路由检测
        $path = $this->app->getRootPath() . 'route' . DIRECTORY_SEPARATOR . ($app ? $app . DIRECTORY_SEPARATOR : '');

        $files = is_dir($path) ? scandir($path) : [];

        foreach ($files as $file) {
            if (strpos($file, '.php')) {
                include $path . $file;
            }
        }

        //触发路由载入完成事件
        $this->app->event->trigger(RouteLoaded::class);

        $content = '<?php ' . PHP_EOL . 'return ';
        $content .= '\think\App::unserialize(\'' . \think\App::serialize($this->app->route->getName()) . '\');';
        return $content;
    }

}
