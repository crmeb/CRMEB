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

namespace crmeb\plugin;

use think\facade\Log;

/**
 * 模块路由加载器
 *
 * 统一封装各应用入口（api 用户端 / adminapi 后台管理端）的模块路由文件扫描加载逻辑：
 * glob 多层目录合并 → 模块启用状态校验 → require 逐个加载。
 *
 * 支持的目录结构：
 * - 一级: modules/{name}/route/{scene}.php（如 agent）
 * - 二级: modules/{type}/{name}/route/{scene}.php（如 channel/wechat_mp、product/normal、trade/seckill）
 *
 * 设计要点：
 * - 路由加载前校验模块启用状态，禁用的模块不加载路由，避免"路由在、Handler 全缺"的脑裂
 * - glob 无匹配时返回空数组兜底，不产生告警
 * - 启动期隔离：单个路由文件语法错误/运行时抛错仅记 error 日志并跳过该模块，
 *   不阻断其他模块路由加载与全站启动
 *
 * 位于 crmeb/ 层，仅依赖同层 ModuleStateChecker，不调任何 app/ 类，符合三层架构依赖铁律。
 */
class ModuleRouteLoader
{
    /**
     * 加载指定场景的模块路由文件
     * @param string $scene 路由场景：user-用户端（api 应用）/ admin-后台管理端（adminapi 应用）
     * @return void
     */
    public static function load(string $scene): void
    {
        // 扫描一、二级模块目录下的路由文件并合并
        $files = array_merge(
            glob(root_path() . "modules/*/route/{$scene}.php") ?: [],
            glob(root_path() . "modules/*/*/route/{$scene}.php") ?: []
        );
        // 过滤掉禁用模块的路由文件后逐个加载
        foreach (array_filter($files, fn($file) => ModuleStateChecker::isModuleRouteActive($file)) as $file) {
            // 启动期隔离：插件路由文件语法错误会抛 ParseError，逐文件 try 保护，
            // 降级行为为仅跳过该模块路由并记录 file/line，不阻断全站路由加载
            try {
                require $file;
            } catch (\Throwable $e) {
                Log::error('模块路由文件加载失败，已跳过 [' . $file . ']：' . $e->getMessage()
                    . ' @ ' . $e->getFile() . ':' . $e->getLine());
            }
        }
    }

    /**
     * 未激活模块提示
     *
     * miss 路由兜底：URL 第一段若匹配某个模块目录名（modules/{name} 或 modules/{type}/{name}），
     * 且该模块存在 {scene} 路由文件但未安装/未启用，则返回模块标识，用于向客户端友好提示"插件未安装"。
     *
     * 基于目录扫描 + ModuleStateChecker 安装状态校验，不硬编码任何模块清单，
     * 模块安装/卸载/启用/禁用后提示自动跟随，符合插件可插拔设计。
     *
     * @param string $scene 路由场景：user-用户端 / admin-后台管理端
     * @return array{key: string, name: string}|null 未激活模块的 key 与中文名；URL 不匹配任何模块或对应模块已激活时返回 null
     */
    public static function inactiveModuleHint(string $scene): ?array
    {
        // 取 URL 第一段作为模块目录名候选（MultiApp 已剥离应用名前缀）
        $prefix = strtolower((string)strtok((string)request()->pathinfo(), '/'));
        if ($prefix === '') {
            return null;
        }
        // 一级模块：modules/{prefix}/route/{scene}.php
        $files = glob(root_path() . "modules/{$prefix}/route/{$scene}.php") ?: [];
        // 二级模块：modules/*/{prefix}/route/{scene}.php
        $files = array_merge($files, glob(root_path() . "modules/*/{$prefix}/route/{$scene}.php") ?: []);
        foreach ($files as $file) {
            $meta = self::readModuleMeta(dirname(dirname($file)));
            // 路由文件存在但模块未激活 => 说明该接口属于未安装/未启用的插件
            if ($meta !== null && !ModuleStateChecker::isActive($meta['key'])) {
                return $meta;
            }
        }
        return null;
    }

    /**
     * 读取模块根目录 plugin.json 的 key 与中文名
     * @param string $moduleRoot 模块根目录绝对路径
     * @return array{key: string, name: string}|null 无 plugin.json 或解析失败返回 null
     */
    protected static function readModuleMeta(string $moduleRoot): ?array
    {
        $pluginFile = $moduleRoot . DIRECTORY_SEPARATOR . 'plugin.json';
        if (!is_file($pluginFile)) {
            return null;
        }
        $meta = json_decode((string)file_get_contents($pluginFile), true);
        if (!is_array($meta) || empty($meta['key'])) {
            return null;
        }
        return [
            'key'  => (string)$meta['key'],
            'name' => !empty($meta['name']) ? (string)$meta['name'] : (string)$meta['key'],
        ];
    }
}
