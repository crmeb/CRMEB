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

namespace app\services\diy;

use crmeb\basic\BaseService;
use crmeb\plugin\ModuleStateChecker;
use crmeb\plugin\PluginManifestRepository;

/**
 * DIY 组件可用性服务（插件可用性判断的唯一所在地）
 *
 * 插件通过 plugin.json 的 diy.components 声明自己提供的 DIY 装修组件（运行时唯一真源），
 * 本服务结合模块安装/启用状态（eb_module_install_log：status=1 AND enabled=1）计算可用性，
 * 按消费方向输出两种口径：
 *
 * - 管理端编辑器：getAvailableComponents() 下发可用组件白名单，编辑器对插件归属组件
 *   （组件源码 pluginComponent 标记）按白名单放行——插件代码被裁剪、未安装或未启用时
 *   组件一律隐藏，画布已存在节点渲染占位（节点保留，保存不丢失，插件重新启用后自动
 *   恢复）。白名单语义保证「清单缺失即隐藏」，避免裁剪后端插件代码后前端组件悬空显示；
 * - 移动端 C 端：filterDiyValue() 仍按不可用清单（getUnavailableComponents()）在数据
 *   出口直接剔除不可用组件节点，前端拿到的即干净数据；数据实时读库，插件重新启用后
 *   自动恢复。
 *
 * 设计要点：
 * - 只读取清单元数据（PluginManifestRepository），不加载任何模块代码，符合三层依赖铁律
 * - ModuleStateChecker 进程内缓存，同一请求只查一次库
 * - 前后端同仓同包发布：插件目录与前端组件文件一起交付/裁剪，清单即完整约束
 */
class DiyComponentServices extends BaseService
{
    /**
     * 核心商品列表类 DIY 组件（非插件归属，固定参与选中列表剥离/回填）
     */
    private const CORE_GOODS_LIST_COMPONENTS = ['goodList'];

    /**
     * 获取当前可用（插件代码存在且已安装并启用）的 DIY 组件白名单
     *
     * 返回结构：组件名 => 组件标题，例如 ['signIn' => '签到']。
     * 仅聚合「已安装且已启用」模块清单中的 diy.components；插件代码被裁剪、未安装或
     * 未启用时组件自然不在名单内，管理端编辑器据此隐藏插件归属组件（fail-closed）。
     * 仅消费组件名，不含任何插件信息，前端无需感知归属来源。
     *
     * @return array<string, string>
     */
    public function getAvailableComponents(): array
    {
        $available = [];
        $activeKeys = ModuleStateChecker::activeKeys();
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->modules() as $manifest) {
            // 注意：eb_module_install_log.module_key 存的是 plugin.json 原始 key（legacyKey），
            // 不是 canonicalId，必须用 legacyKey 匹配活跃集合
            if (!in_array($manifest->identity->legacyKey, $activeKeys, true)) {
                continue;
            }
            foreach ($manifest->meta['diy']['components'] ?? [] as $component) {
                $name = is_array($component) ? (string)($component['name'] ?? '') : '';
                if ($name !== '') {
                    $available[$name] = (string)($component['title'] ?? '');
                }
            }
        }
        return $available;
    }

    /**
     * 获取当前不可用（插件未安装或未启用）的 DIY 组件清单
     *
     * 返回结构：组件名 => 组件标题，例如 ['seckill' => '秒杀']。
     * 组件名与 DIY 数据节点 name 一致（管理端对应组件 defaultName）。
     * 供移动端 C 端数据出口 filterDiyValue() 剔除节点使用；管理端编辑器已改为
     * 白名单口径（getAvailableComponents()），不再消费本清单。
     *
     * @return array<string, string>
     */
    public function getUnavailableComponents(): array
    {
        $unavailable = [];
        $activeKeys = ModuleStateChecker::activeKeys();
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->modules() as $manifest) {
            // 注意：eb_module_install_log.module_key 存的是 plugin.json 原始 key（legacyKey），
            // 不是 canonicalId，必须用 legacyKey 匹配活跃集合
            if (in_array($manifest->identity->legacyKey, $activeKeys, true)) {
                continue;
            }
            foreach ($manifest->meta['diy']['components'] ?? [] as $component) {
                $name = is_array($component) ? (string)($component['name'] ?? '') : '';
                if ($name !== '') {
                    $unavailable[$name] = (string)($component['title'] ?? '');
                }
            }
        }
        return $unavailable;
    }

    /**
     * 过滤 DIY 数据中的不可用组件节点（C 端数据出口调用，运行时过滤，不落库）
     *
     * 支持三种数据形态：
     * - 节点列表：[{name: 'xxx', ...}, ...]
     * - 节点映射：{num: {name: 'xxx', ...}, ...}（编辑器保存的 DIY 模板）
     * - 组件映射：{组件名: [配置...], ...}（按选中商品剥离/回填的列表数据）
     *
     * @param mixed $value
     * @return mixed 过滤后的数据，结构保持不变；非数组原样返回
     */
    public function filterDiyValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        $unavailable = array_keys($this->getUnavailableComponents());
        if ($unavailable === []) {
            return $value;
        }
        $hitByName = static function ($item) use ($unavailable): bool {
            return is_array($item) && isset($item['name']) && in_array($item['name'], $unavailable, true);
        };
        if (array_is_list($value)) {
            return array_values(array_filter(
                $value,
                static fn ($item) => !is_array($item) || !isset($item['name']) || !in_array($item['name'], $unavailable, true)
            ));
        }
        // 关联对象：按「组件名键」或「值内节点 name」双向命中（两种映射形态统一处理）
        return array_filter($value, static function ($item, $key) use ($unavailable, $hitByName): bool {
            if (in_array((string)$key, $unavailable, true)) {
                return false;
            }
            return !$hitByName($item);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 获取需要剥离/回填选中商品列表的 DIY 组件 key 清单
     *
     * 核心 goodList 组件 + 各插件 plugin.json 中 diy.components 自声明的组件名。
     * 新活动插件只需在自己的清单里声明组件，保存时剥离选中列表、读取时按 ids
     * 回填的逻辑即自动覆盖，无需修改核心代码。
     *
     * 注意：该清单只服务于商品列表剥离/回填，不参与组件可用性判定。
     *
     * @return string[]
     */
    public function getGoodsListComponentKeys(): array
    {
        $keys = self::CORE_GOODS_LIST_COMPONENTS;
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->modules() as $manifest) {
            foreach ($manifest->meta['diy']['components'] ?? [] as $component) {
                $name = is_array($component) ? (string)($component['name'] ?? '') : '';
                if ($name !== '') {
                    $keys[] = $name;
                }
            }
        }
        return array_values(array_unique($keys));
    }
}
