<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class ThinkExtend extends LibraryInstaller
{

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->copyExtraFiles($package);
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->copyExtraFiles($target);

    }

    protected function copyExtraFiles(PackageInterface $package)
    {
        if ($this->composer->getPackage()->getType() == 'project') {

            $extra = $package->getExtra();

            if (!empty($extra['think-config'])) {

                $composerExtra = $this->composer->getPackage()->getExtra();

                $appDir = !empty($composerExtra['app-path']) ? $composerExtra['app-path'] : 'application';

                if (is_dir($appDir)) {

                    $extraDir = $appDir . DIRECTORY_SEPARATOR . 'extra';
                    $this->filesystem->ensureDirectoryExists($extraDir);

                    //配置文件
                    foreach ((array) $extra['think-config'] as $name => $config) {
                        $target = $extraDir . DIRECTORY_SEPARATOR . $name . '.php';
                        $source = $this->getInstallPath($package) . DIRECTORY_SEPARATOR . $config;

                        if (is_file($target)) {
                            $this->io->write("<info>File {$target} exist!</info>");
                            continue;
                        }

                        if (!is_file($source)) {
                            $this->io->write("<info>File {$target} not exist!</info>");
                            continue;
                        }

                        copy($source, $target);
                    }
                }
            }
        }
    }

    public function supports($packageType)
    {
        return 'think-extend' === $packageType;
    }
}