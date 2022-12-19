<?php

namespace app\jobs;

use app\services\system\UpgradeServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 升级包
 * Class UpgradeJob
 * @package app\jobs
 */
class UpgradeJob extends BaseJobs
{
    use QueueTrait;

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
}