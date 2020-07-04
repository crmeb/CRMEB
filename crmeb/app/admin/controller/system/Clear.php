<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\CacheService;

/**
 * 首页控制器
 * Class Clear
 * @package app\admin\controller
 *
 */
class Clear extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 刷新数据缓存
     */
    public function refresh_cache()
    {
        $root       = app()->getRootPath() . 'runtime' . DS;
        $adminRoute = $root . 'admin';
        $apiRoute   = $root . 'api';
        $cacheRoute = $root . 'cache';
        $cache      = [];

        if (is_dir($adminRoute))
            $cache[$adminRoute] = scandir($adminRoute);
        if (is_dir($apiRoute))
            $cache[$apiRoute] = scandir($apiRoute);
        if (is_dir($cacheRoute))
            $cache[$cacheRoute] = scandir($cacheRoute);

        foreach ($cache as $p => $list) {
            foreach ($list as $file) {
                if (!in_array($file, ['.', '..', 'log', 'schema', 'route.php'])) {
                    $path = $p . DS . $file;
                    if (is_file($path)) {
                        @unlink($path);
                    } else {
                        $this->delDirAndFile($path . DS);
                    }
                }
            }
        }
        CacheService::clear();
        return app('json')->successful('数据缓存刷新成功!');
    }


    /**
     * 删除日志
     */
    public function delete_log()
    {
        $root = app()->getRootPath() . 'runtime' . DS;
        $this->delDirAndFile($root . 'admin' . DS . 'log' . DS);
        $this->delDirAndFile($root . 'api' . DS . 'log' . DS);
        $this->delDirAndFile($root . 'log' . DS);

        return app('json')->successful('数据缓存刷新成功!');
    }

    /** 递归删除文件
     * @param $dirName
     * @param bool $subdir
     */
    protected function delDirAndFile($dirName)
    {
        $list = glob($dirName . '*');
        foreach ($list as $file) {
            if (is_dir($file))
                $this->delDirAndFile($file . DS);
            else
                @unlink($file);
        }
        @rmdir($dirName);
    }
}


