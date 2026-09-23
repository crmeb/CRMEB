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

use DirectoryIterator;
use InvalidArgumentException;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\event\RouteLoaded;
use Throwable;

class Route extends Command
{
    use Discoverable;

    protected function configure()
    {
        $this->setName('optimize:route')
            ->addArgument('dir', Argument::OPTIONAL, 'dir name .')
            ->setDescription('Build app route cache.');
    }

    protected function execute(Input $input, Output $output)
    {
        $dirs = ((array) $input->getArgument('dir')) ?: $this->getDefaultDirs();

        foreach ($dirs as $dir) {
            $path = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . ($dir ? $dir . DIRECTORY_SEPARATOR : '');
            try {
                $cache = $this->buildRouteCache($dir);
                if (! is_dir($path)) {
                    mkdir($path, 0755, true);
                }
                file_put_contents($path . 'route.php', $cache);
            } catch (Throwable $e) {
                $output->warning($e->getMessage());
            }
        }

        $output->info('Succeed!');
    }

    protected function scanRoute($path, $root, $autoGroup)
    {
        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }

            if ($fileinfo->getType() == 'file' && $fileinfo->getExtension() == 'php') {
                $groupName = str_replace('\\', '/', substr_replace($fileinfo->getPath(), '', 0, strlen($root)));
                if ($groupName) {
                    $this->app->route->group($groupName, function () use ($fileinfo) {
                        include $fileinfo->getRealPath();
                    });
                } else {
                    include $fileinfo->getRealPath();
                }
            } elseif ($autoGroup && $fileinfo->isDir()) {
                $this->scanRoute($fileinfo->getPathname(), $root, $autoGroup);
            }
        }
    }

    protected function buildRouteCache(?string $dir = null): string
    {
        $this->app->route->clear();
        $this->app->route->lazy(false);

        // 路由检测
        $autoGroup = $this->app->route->config('route_auto_group');
        $path = $this->app->getRootPath() . ($dir ? 'app' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR : '') . 'route' . DIRECTORY_SEPARATOR;
        if (! is_dir($path)) {
            throw new InvalidArgumentException("{$path} directory does not exist");
        }

        $this->scanRoute($path, $path, $autoGroup);

        //触发路由载入完成事件
        $this->app->event->trigger(RouteLoaded::class);
        $rules = $this->app->route->getName();

        return '<?php ' . PHP_EOL . 'return ' . var_export($rules, true) . ';';
    }

    /**
     * 获取默认目录名
     * @return array<int, ?string>
     */
    private function getDefaultDirs(): array
    {
        // 判断是否使用多应用模式
        // 如果使用了则扫描 app 目录
        // 否则返回 null，让其扫描根目录的 route 目录
        return $this->isInstalledMultiApp()
            ? $this->discoveryMultiAppDirs('route')
            : [null];
    }
}
