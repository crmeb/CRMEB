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
namespace app\jobs;

use app\services\system\log\SystemFileMd5Services;
use app\services\system\UpgradeServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

/**
 * 升级包
 * Class UpgradeJob
 * @package app\jobs
 */
class UpgradeJob extends BaseJobs
{

    /**
     * 下载
     * @param $seq
     * @param $url
     * @param $filePath
     * @param $filename
     * @param $timeout
     * @return bool
     */
    public function download($seq, $url, $filePath, $filename, $timeout): bool
    {
        try {
            /** @var UpgradeServices $services */
            $services = app()->make(UpgradeServices::class);
            $services->download($seq, $url, $filePath, $filename, $timeout);
        } catch (\Exception $e) {
            Log::error('升级包下载失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 数据库备份
     * @param $token
     * @return bool
     */
    public function databaseBackup($token): bool
    {
        try {
            /** @var UpgradeServices $services */
            $services = app()->make(UpgradeServices::class);
            $services->databaseBackup($token);
        } catch (\Exception $e) {
            Log::error('数据库备份失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 项目备份
     * @param $token
     * @return bool
     */
    public function projectBackup($token): bool
    {
        try {
            /** @var UpgradeServices $services */
            $services = app()->make(UpgradeServices::class);
            $services->projectBackup($token);
        } catch (\Exception $e) {
            Log::error('项目备份失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 覆盖项目文件
     * @param $token
     * @return bool
     */
    public function coverageProject($token): bool
    {
        try {
            /** @var UpgradeServices $services */
            $services = app()->make(UpgradeServices::class);
            $services->coverageProject($token);
        } catch (\Exception $e) {
            Log::error('覆盖项目失败,失败原因:' . $e->getMessage());
            // 失败时也要设置状态，避免无限循环
            \crmeb\services\CacheService::set($token . '_coverage_project', -1, 86400);
            \crmeb\services\CacheService::set($token . 'upgrade_status', -1, 86400);
            \crmeb\services\CacheService::set($token . 'upgrade_status_tip', '覆盖项目失败: ' . $e->getMessage(), 86400);
        }
        return true;
    }

    /**
     * 检查文件MD5
     * @param $token
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/26
     */
    public function checkFileMd5($token)
    {
        try {
            /** @var SystemFileMd5Services $services */
            $services = app()->make(SystemFileMd5Services::class);
            $data = $services->checkFile();
            \crmeb\services\CacheService::set($token . '_check_md5_file', $data, 86400);
            \crmeb\services\CacheService::set($token . '_check_md5_status', empty($data) ? 2 : 1, 86400);
        } catch (\Exception $e) {
            Log::error('检查文件MD5失败,失败原因:' . $e->getMessage());
        }
        return true;
    }
}
