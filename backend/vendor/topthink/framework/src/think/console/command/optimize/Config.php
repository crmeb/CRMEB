<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\console\command\optimize;

use InvalidArgumentException;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use Throwable;

class Config extends Command
{
    use Discoverable;

    protected function configure()
    {
        $this->setName('optimize:config')
            ->addArgument('dir', Argument::OPTIONAL, 'dir name .')
            ->setDescription('Build config cache.');
    }

    protected function execute(Input $input, Output $output)
    {
        $dirs = ((array) $input->getArgument('dir')) ?: $this->getDefaultDirs();

        foreach ($dirs as $dir) {
            $path = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . ($dir ? $dir . DIRECTORY_SEPARATOR : '');
            try {
                $cache = $this->buildCache($dir);
                if (! is_dir($path)) {
                    mkdir($path, 0755, true);
                }
                file_put_contents($path . 'config.php', $cache);
            } catch (Throwable $e) {
                $output->warning($e->getMessage());
            }
        }

        $output->info('Succeed!');
    }

    private function buildCache(?string $dir = null): string
    {
        $path = $this->app->getRootPath() . ($dir ? 'app' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR : '') . 'config' . DIRECTORY_SEPARATOR;
        if (! is_dir($path)) {
            throw new InvalidArgumentException("{$path} directory does not exist");
        }

        // 使用 clone 防止多应用配置污染
        $config = clone $this->app->config;
        if (is_dir($path)) {
            $files = glob($path . '*' . $this->app->getConfigExt());
            foreach ($files as $file) {
                $config->load($file, pathinfo($file, PATHINFO_FILENAME));
            }
        }

        return '<?php ' . PHP_EOL . 'return ' . var_export($config->get(), true) . ';';
    }

    /**
     * 获取默认目录名
     * @return array<int, ?string>
     */
    private function getDefaultDirs(): array
    {
        // 包含全局应用配置目录
        $dirs = [null];
        if ($this->isInstalledMultiApp()) {
            $dirs = array_merge($dirs, $this->discoveryMultiAppDirs('config'));
        }
        return $dirs;
    }
}
