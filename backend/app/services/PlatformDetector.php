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
declare (strict_types=1);

namespace app\services;

use crmeb\plugin\PluginManager;

/**
 * 平台自动识别服务
 * 从统一插件注册中心（ExtensionRegistry）读取所有已启用的端渠道模块
 * 每个端渠道通过 App::detect() 自描述检测逻辑
 * 新增平台只需在 modules/channel/ 下创建新目录并声明 plugin.json（type=channel），无需改代码
 * Class PlatformDetector
 * @package app\services
 */
class PlatformDetector
{
    /**
     * 检测结果缓存
     * @var string|null
     */
    protected static ?string $cachedPlatform = null;

    /**
     * 已注册的平台列表缓存
     * @var array|null
     */
    protected static ?array $registeredPlatforms = null;

    /**
     * 自动识别当前请求的平台
     * 遍历 modules/channel/ 下所有端渠道模块，调用 App::detect() 匹配
     * @return string 平台标识（目录名），无匹配时返回 'h5'
     */
    public static function detect(): string
    {
        if (self::$cachedPlatform !== null) {
            return self::$cachedPlatform;
        }

        $platforms = self::getRegisteredPlatforms();

        foreach ($platforms as $key => $info) {
            $appClass = $info['class'];
            if (class_exists($appClass) && method_exists($appClass, 'detect') && $appClass::detect()) {
                self::$cachedPlatform = $key;
                return self::$cachedPlatform;
            }
        }

        self::$cachedPlatform = 'h5';
        return self::$cachedPlatform;
    }

    /**
     * 获取当前平台的显示名称
     * @return string
     */
    public static function getPlatformName(): string
    {
        $platform = self::detect();
        $platforms = self::getRegisteredPlatforms();
        return $platforms[$platform]['name'] ?? $platform;
    }

    /**
     * 获取所有已注册的平台列表（从统一插件注册中心读取）
     *
     * 渠道模块通过 plugin.json 声明 type=channel，由 ExtensionRegistry 在启动期统一发现注册
     * （含 ModuleStateChecker 启用白名单过滤，禁用渠道不参与平台检测），
     * 平台 key 取 path 末段目录名（与渠道目录名约定一致），App 类按注册元数据 path 推导。
     *
     * @return array [platform_key => ['class' => ..., 'name' => ...]]
     */
    public static function getRegisteredPlatforms(): array
    {
        if (self::$registeredPlatforms !== null) {
            return self::$registeredPlatforms;
        }

        $platforms = [];

        // 从统一插件注册中心读取已启用的渠道模块（AppService::boot 时已 discover 完成）
        $registry = PluginManager::getRegistry();
        if ($registry === null) {
            // 启动极早期（boot 前）被调用的兜底：无注册表时视为无平台，detect() 走 h5 兜底
            self::$registeredPlatforms = $platforms;
            return $platforms;
        }

        foreach ($registry->getByType('channel') as $info) {
            // meta.path 形如 modules/channel/wechat_mini，校验渠道目录约定防止 path 误填
            $path = trim((string)($info->meta['path'] ?? ''), '/');
            if (!str_starts_with($path, 'modules/channel/')) {
                continue;
            }

            // 平台 key = path 末段目录名（与渠道目录名一致，保持 Form-type 匹配语义不变）
            $name = basename($path);
            // 按注册元数据推导渠道 App 类（与 ExtensionRegistry::discover 推导 Handler 同一约定）
            $appClass = '\\' . str_replace('/', '\\', $path) . '\\App';

            // 渠道 App 必须存在且有 detect 方法才参与平台检测
            if (!class_exists($appClass) || !method_exists($appClass, 'detect')) {
                continue;
            }

            $platforms[$name] = [
                'class' => $appClass,
                'name' => method_exists($appClass, 'getName') ? $appClass::getName() : $name,
            ];
        }

        self::$registeredPlatforms = $platforms;
        return $platforms;
    }

    /**
     * 清除缓存（用于测试）
     */
    public static function clearCache(): void
    {
        self::$cachedPlatform = null;
        self::$registeredPlatforms = null;
    }
}
