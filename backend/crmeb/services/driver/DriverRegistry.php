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

declare(strict_types=1);

namespace crmeb\services\driver;

use crmeb\plugin\ModuleStateChecker;
use think\facade\Db;

/**
 * 驱动注册表（eb_driver 统一运行时入口）
 *
 * 职责：从统一驱动字典读取已安装驱动，并结合插件生命周期过滤可加载项。
 *
 * plugin.json 结构约定：
 *   - 顶层为插件元信息（key/name/type/path 等，由 ModuleInstaller 生命周期消费）
 *   - driver 子对象承载驱动的全量描述字段（driver_key/name/class_name/icon/
 *     capabilities/config_fields/help_doc/sort/is_builtin 及各类型扩展字段），
 *     Registry 对其做全量透传，不维护字段白名单
 *
 * Class DriverRegistry
 * @package crmeb\services\driver
 */
class DriverRegistry
{
    /**
     * 驱动字典缓存（按驱动类型分组，避免同请求内重复查库）
     * @var array
     */
    private static array $cache = [];

    /** 生命周期状态变化后清空进程内字典缓存。 */
    public static function resetCache(): void
    {
        self::$cache = [];
    }

    /**
     * 从统一驱动字典读取指定类型的已安装驱动。
     * plugin.json 只在插件安装阶段读取，运行时不再扫描目录。
     * @param string $type 驱动类型目录名（如 sms/pay/upload/email）
     * @return array [driver_key => 驱动信息数组（plugin.json driver 子对象全量透传 + namespace）]
     */
    public static function scan(string $type): array
    {
        $type = strtolower(trim($type));
        if (isset(self::$cache[$type])) {
            return self::$cache[$type];
        }

        $drivers = [];
        try {
            $rows = Db::name('driver')
                ->where('driver_type', $type)
                ->order('sort', 'desc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
        } catch (\Throwable $e) {
            return self::$cache[$type] = [];
        }

        $jsonFields = [
            'capabilities', 'config_fields', 'help_doc', 'bucket_fields',
            'thumb_watermark_config', 'platforms',
        ];
        foreach ($rows as $row) {
            $pluginId = (string) ($row['owner_plugin_id'] ?? '');
            if ($pluginId === '' || !ModuleStateChecker::isDriverActive($pluginId)) {
                continue;
            }

            foreach ($jsonFields as $field) {
                $value = $row[$field] ?? [];
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    $row[$field] = is_array($decoded) ? $decoded : [];
                }
            }
            $namespace = (string) ($row['namespace'] ?? '');
            $parts = explode('\\', $namespace);
            $dirName = (string) end($parts);
            $info = $row + [
                'class_name' => 'Driver',
                'icon' => '',
                'capabilities' => [],
                'config_fields' => [],
                'help_doc' => [],
                'sort' => 0,
                'is_builtin' => 0,
            ];
            $info['name'] = (string) ($row['driver_name'] ?? $dirName);
            $info['dir_name'] = $dirName;
            $info['plugin_id'] = $pluginId;
            $info['plugin_key'] = (string) ($row['driver_key'] ?? '');
            $info['namespace'] = $namespace;
            $drivers[$info['driver_key']] = $info;
        }

        return self::$cache[$type] = $drivers;
    }

    /**
     * 获取单个驱动信息
     * @param string $type 驱动类型目录名
     * @param string $key 驱动标识（driver_key）
     * @return array|null 不存在时返回 null
     */
    public static function get(string $type, string $key): ?array
    {
        return self::scan($type)[$key] ?? null;
    }

    /**
     * 获取单个驱动的完整类名
     * @param string $type 驱动类型目录名
     * @param string $key 驱动标识（driver_key）
     * @return string|null 形如 driver\sms\Aliyun\Driver 的类名，不存在时返回 null
     */
    public static function getDriverClass(string $type, string $key): ?string
    {
        $driver = self::get($type, $key);
        if (!$driver) {
            return null;
        }
        return $driver['namespace'] . '\\' . ($driver['class_name'] ?? 'Driver');
    }
}
