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

use app\services\diy\ThemeDownloadServices;
use app\services\diy\ThemeServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

/**
 * 主题导出队列任务
 * Class ThemeExportJob
 * @package app\jobs
 */
class ThemeExportJob extends BaseJobs
{

    /**
     * 执行主题导出任务
     * 1. 打包主题文件生成 zip
     * 2. 将 download_url 写回 eb_theme_download 记录
     *
     * @param $info 主题信息
     * @param int $recordId eb_theme_download 记录ID
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function export($info, int $recordId): bool
    {
        try {
            /** @var ThemeServices $themeServices */
            $themeServices = app()->make(ThemeServices::class);
            /** @var ThemeDownloadServices $themeDownloadServices */
            $themeDownloadServices = app()->make(ThemeDownloadServices::class);

            $downloadUrl = $themeServices->exportThemePackage($info);

            // 将生成的下载地址写回下载记录
            $themeDownloadServices->updateDownloadUrl($recordId, $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('主题导出队列失败，原因：' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            return false;
        }
        return true;
    }
}
