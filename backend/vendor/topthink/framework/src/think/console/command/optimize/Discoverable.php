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

use Composer\InstalledVersions;
use DirectoryIterator;

trait Discoverable
{
    /**
     * 判断是否安装 topthink/think-multi-app
     */
    private function isInstalledMultiApp(): bool
    {
        return InstalledVersions::isInstalled('topthink/think-multi-app');
    }

    /**
     * 发现多应用程序目录
     * @return string[]
     */
    private function discoveryMultiAppDirs(string $directoryName): array
    {
        $dirs = [];
        foreach (new DirectoryIterator($this->app->getAppPath()) as $item) {
            if (! $item->isDir() || $item->isDot()) {
                continue;
            }
            $path = $item->getRealPath() . DIRECTORY_SEPARATOR . $directoryName . DIRECTORY_SEPARATOR;
            if (is_dir($path)) {
                $dirs[] = $item->getFilename();
            }
        }
        return $dirs;
    }
}
