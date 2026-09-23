<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system\log;

use app\dao\system\log\SystemFileMd5Dao;
use crmeb\basic\BaseService;
use think\facade\Config;
use think\facade\Db;

/**
 * 文件 MD5 校验日志服务
 * 本类为系统文件校验工具，Db::execute 用于清空校验表，不属于业务数据操作，保留直接调用。
 */
class SystemFileMd5Services extends BaseService
{
    /**
     * @var SystemFileMd5Dao
     */
    protected $dao;

    public function __construct(SystemFileMd5Dao $dao)
    {
        $this->dao = $dao;
    }

    public function saveMd5List(array $list)
    {
        if (empty($list)) return true;
        return (bool)$this->transaction(function () use ($list) {
            return $this->dao->saveAll($list);
        });
    }

    public function clearMd5List()
    {
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        Db::execute('TRUNCATE TABLE `' . $prefix . 'system_file_md5`');
    }

    /**
     * 写入文件 md5 快照
     * 扫描 app/crmeb 目录下所有 .php 文件，清空校验表后批量写入
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/25
     */
    public function writeMd5()
    {
        $rootPath = app()->getRootPath();
        $files = array_merge(
            $this->getDir($rootPath . 'app'),
            $this->getDir($rootPath . 'crmeb')
        );
        // 只找 .php 文件
        $files = array_filter($files, function ($path) {
            return pathinfo($path, PATHINFO_EXTENSION) === 'php';
        });
        $len = strlen($rootPath);
        $list = [];
        foreach ($files as $path) {
            $list[] = [
                'filename' => substr($path, $len),
                'md5' => md5_file($path),
            ];
        }

        $this->clearMd5List();
        $this->saveMd5List($list);

        return true;
    }

    public function checkFile()
    {
        $rootPath = app()->getRootPath();
        $files = array_merge(
            $this->getDir($rootPath . 'app'),
            $this->getDir($rootPath . 'crmeb')
        );
        // 只找 .php 文件
        $files = array_filter($files, function ($path) {
            return pathinfo($path, PATHINFO_EXTENSION) === 'php';
        });
        $len = strlen($rootPath);
        // 当前列表
        $list = $this->dao->getColumn([], 'md5', 'filename');
        $currentList = [];
        foreach ($files as $path) {
            $currentList[substr($path, $len)] = md5_file($path);
        }

        // 忽略app/adminapi/controller/UpgradeController.php文件的改动
        unset($currentList['app/adminapi/controller/UpgradeController.php']);
        unset($list['app/adminapi/controller/UpgradeController.php']);

        $diffFiles = [];
        $newFiles = array_keys(array_diff_key($currentList, $list));
        $delFiles = array_keys(array_diff_key($list, $currentList));
        $changedFiles = array_keys(array_diff_assoc($currentList, $list));
        return array_values(array_unique(array_merge($newFiles, $delFiles, $changedFiles)));
    }

    public function getDir($dir)
    {
        $data = [];
        $this->searchDir($dir, $data);
        return $data;
    }

    public function searchDir($path, &$data)
    {
        if (is_dir($path) && !strpos($path, 'uploads')) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->searchDir($path . '/' . $file, $data);
                }
            }
        }
        if (is_file($path)) {
            $data[] = $path;
        }
    }
}
