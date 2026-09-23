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

namespace app\services\message\pipeline;

use app\services\message\admin\NoticeConfigServices;
use crmeb\services\CacheService;
use think\facade\Log;

/**
 * 通知配置查询服务（带缓存）
 *
 * 从 eb_notice_config 表查询通知配置并按 mark 分组缓存，
 * 供 Notice 门面使用；channels 子数组含渠道表合并的
 * 开关/支持范围/适配器类名（adapter_class），发送侧据此选渠道并实例化适配器。
 * 渠道适配器实现统一位于 app/services/message/channel/ 与 modules 层渠道模块。
 */
class SendNoticeService
{
    /**
     * 通知配置缓存键名
     */
    const CACHE_KEY = 'notice_config_all';

    /**
     * 缓存有效期（秒）- 默认24小时
     */
    const CACHE_EXPIRE = 86400;

    /**
     * 获取通知配置（带缓存）
     *
     * @param string $mark 消息标识
     * @return array 配置数组（同mark可能有type=1和type=2两条配置）
     */
    public function getNoticeInfo(string $mark): array
    {
        $cacheKey = self::CACHE_KEY;

        try {
            $allConfig = CacheService::get($cacheKey);
            if ($allConfig) {
                return $allConfig[$mark] ?? [];
            }
        } catch (\Throwable $e) {
        }

        /** @var NoticeConfigServices $services */
        $services = app()->make(NoticeConfigServices::class);
        $allConfig = $services->getAllNotce();

        $configMap = [];
        foreach ($allConfig as $item) {
            $itemArr = is_object($item) ? $item->toArray() : $item;
            $configMap[$itemArr['mark']][] = $itemArr;
        }

        try {
            CacheService::set($cacheKey, $configMap, self::CACHE_EXPIRE);
        } catch (\Throwable $e) {
        }

        return $configMap[$mark] ?? [];
    }

    /**
     * 清除缓存（通知配置）
     */
    public function clearCache(): void
    {
        try {
            CacheService::delete(self::CACHE_KEY);
        } catch (\Throwable $e) {
            Log::error('清除通知缓存失败: ' . $e->getMessage());
        }
    }
}
