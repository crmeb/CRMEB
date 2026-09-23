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

namespace crmeb\interfaces;

/**
 * 模块安装器接口
 * 产品类型和营销活动均实现此接口，实现安装/卸载能力
 */
interface ModuleInstallerInterface
{
    /**
     * 模块标识（唯一）
     * 产品类型：product/{type_key}，如 product/hotel
     * 营销活动：activity/{type_key}，如 activity/seckill
     * @return string
     */
    public function getKey(): string;

    /**
     * 模块名称（展示用）
     * @return string
     */
    public function getName(): string;

    /**
     * 模块类型：product_type / activity
     * @return string
     */
    public function getType(): string;

    /**
     * 安装
     * 执行顺序：建表 → 插菜单 → 种子数据
     * @return array ['status' => 'success|skipped', 'steps' => string[]]
     */
    public function install(): array;

    /**
     * 卸载
     * 执行顺序：清理数据 → 删菜单 → 删表
     * @return array ['status' => 'success|skipped', 'steps' => string[]]
     */
    public function uninstall(): array;

    /**
     * 是否已安装
     * @return bool
     */
    public function isInstalled(): bool;

    /**
     * 安装前置检查
     * 检查依赖、环境要求等
     * @return array ['errors' => string[]]
     */
    public function checkRequirements(): array;

    /**
     * 启用时回调
     * 子类可重写以部署驱动文件等
     * @return void
     */
    public function onEnable(): void;

    /**
     * 禁用时回调
     * 子类可重写以移除驱动文件等
     * @return void
     */
    public function onDisable(): void;
}
