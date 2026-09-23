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

namespace modules\channel\h5;

use crmeb\module\BaseModuleInstaller;
use think\facade\Db;

/**
 * H5浏览器端渠道安装器
 *
 * channel 为容器目录，H5端作为其下独立可安装/卸载的端渠道。
 * getKey 形如 channel/h5，由 ModuleInstaller 扫描 modules/channel/*\/Installer.php 注册。
 *
 * H5 为商城默认端渠道，全新安装站点默认启用（plugin.json 标记 default_install=true，
 * 由 ModuleInstaller::installDefault() 统一安装；本安装器负责创建菜单/tab/配置，幂等兼容）。
 *
 * 数据存储说明：
 * - 无自有数据表，使用商城核心共享表（eb_user 等），以 user_type='h5' 区分端标识。
 * - H5配置（系统配置 tab h5 / 菜单「H5浏览器」/ 相关配置项）由本安装器创建，
 *   卸载时清理，与 app/wechat_mini 渠道模式一致。
 */
class Installer extends BaseModuleInstaller
{
    public function getKey(): string  { return 'channel/h5'; }
    public function getName(): string { return 'H5浏览器'; }
    public function getType(): string { return 'channel'; }

    /**
     * H5端相关 system_config 配置项键名
     * 用于 seed 写入和 cleanData 清理
     */
    private const H5_CONFIG_KEYS = [
        'h5_logo',
        'h5_share_title',
        'h5_share_img',
        'h5_share_synopsis',
    ];

    protected function runMigrate(): void
    {
        // H5端无自有数据表
    }

    protected function rollbackMigrate(): void
    {
        // H5端无自有数据表
    }

    /**
     * 后台菜单配置
     *
     * 菜单树：
     *
     * 应用 (pid=0, 系统已有)
     * └── H5浏览器 (pid=应用, auth=1, menu_path=/setting/system_config/{h5_tab_id}, 系统配置页)
     *
     * 「H5浏览器」一级菜单由本安装器创建，通过 core:admin-app 稳定标识挂载到系统"应用"分组下；
     * menu_path 动态拼接 h5 配置分类 id，首次安装 insertMenus 阶段分类尚未创建时
     * 以 /setting/system_config/0 占位，由 seed() 创建分类后回填。
     */
    protected function getMenuConfig(): array
    {
        return [
            // ========== 一级：H5浏览器（挂载到"应用"下） ==========
            [
                'parent_key'   => 'core:admin-app',
                'icon'         => 'CRMEB-380-yidongduan-xianxing',
                'menu_name'    => 'H5浏览器',
                'menu_path'    => '/setting/system_config/' . $this->getH5TabId(),
                'sort'         => 3,
                'is_show'      => 1,
                'is_show_path' => 1,
                'access'       => 1,
                'auth_type'    => 1,
                'header'       => 'app',
                'is_header'    => 1,
                'unique_auth'  => 'admin-h5',
                'mark'         => 'H5浏览器',
                'params'       => '[]',
            ],
        ];
    }

    /**
     * 获取 H5配置分类（eng_title=h5）的 id
     *
     * 用于拼接「H5浏览器」菜单的 menu_path（/setting/system_config/{id}）。
     * 首次安装 insertMenus 阶段分类尚未创建时返回 0 占位，
     * 由 seed() 创建分类后回填真实路径。
     * @return int
     */
    protected function getH5TabId(): int
    {
        $tab = Db::table($this->table('system_config_tab'))
            ->where('eng_title', 'h5')
            ->find();
        return $tab ? (int)$tab['id'] : 0;
    }

    /**
     * 种子数据：写入 H5配置 tab 与相关 system_config 配置项
     *
     * 安装时确保 eb_system_config_tab 中 h5 分类存在（挂载在"应用配置"下，menus_id 动态绑定「H5浏览器」菜单），
     * 并写入以下配置项到 eb_system_config 表（幂等，已存在则跳过）：
     * - H5端LOGO
     * - H5 分享标题 / 分享图片 / 分享简介
     *
     * @return int 写入的配置项与分类数量
     */
    protected function seed(): int
    {
        $count = 0;

        // 1. 确保配置分类 tab 存在（menus_id 动态绑定「H5浏览器」菜单）
        $h5MenuId = (int) Db::table($this->table('system_menus'))
            ->where('menu_name', 'H5浏览器')
            ->where('is_del', 0)
            ->value('id');

        $tab = $this->ensureConfigTab('h5', [
            'pid'       => 78,
            'title'     => 'H5配置',
            'eng_title' => 'h5',
            'status'    => 1,
            'info'      => 0,
            'icon'      => 'el-icon-Mobile-phone',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => $h5MenuId ?: 0,
        ]);
        if ($tab['created']) {
            $count++;
        }

        // 回填「H5浏览器」菜单路径：insertMenus 阶段 h5 分类尚未创建，
        // menu_path 为占位 /setting/system_config/0，此处更新为真实分类 id
        if ($tab['id'] && $h5MenuId) {
            Db::table($this->table('system_menus'))
                ->where('id', $h5MenuId)
                ->update(['menu_path' => '/setting/system_config/' . $tab['id']]);
        }

        // 2. 写入配置项
        $configs = [
            [
                'menu_name'     => 'h5_logo',
                'config_tab_id' => $tab['id'],
                'type'          => 'upload',
                'input_type'    => 'input',
                'upload_type'   => 1,
                'value'         => '""',
                'info'          => 'H5端LOGO',
                'desc'          => 'H5端首页及分享展示的商城LOGO',
                'sort'          => 0,
            ],
            [
                'menu_name'     => 'h5_share_title',
                'config_tab_id' => $tab['id'],
                'type'          => 'text',
                'input_type'    => 'input',
                'required'      => 'required:true',
                'width'         => 100,
                'value'         => '""',
                'info'          => 'H5分享标题',
                'desc'          => 'H5页面分享到微信时的标题',
                'sort'          => 0,
            ],
            [
                'menu_name'     => 'h5_share_img',
                'config_tab_id' => $tab['id'],
                'type'          => 'upload',
                'input_type'    => 'input',
                'upload_type'   => 1,
                'value'         => '""',
                'info'          => 'H5分享图片',
                'desc'          => 'H5页面分享到微信时展示的图片，图片比例5:4，建议小于50KB',
                'sort'          => 0,
            ],
            [
                'menu_name'     => 'h5_share_synopsis',
                'config_tab_id' => $tab['id'],
                'type'          => 'textarea',
                'input_type'    => '',
                'width'         => 100,
                'high'          => 5,
                'value'         => '""',
                'info'          => 'H5分享简介',
                'desc'          => 'H5页面分享到微信时的简介',
                'sort'          => 0,
            ],
        ];

        foreach ($configs as $config) {
            $exists = Db::table($this->table('system_config'))
                ->where('menu_name', $config['menu_name'])
                ->find();
            if ($exists) {
                continue;
            }

            // 合并默认值
            $defaults = [
                'parameter'   => '',
                'upload_type' => 1,
                'required'    => '',
                'width'       => 0,
                'high'        => 0,
                'status'      => 1,
                'level'       => 0,
                'link_id'     => 0,
                'link_value'  => 0,
            ];

            Db::table($this->table('system_config'))->insert(array_merge($defaults, $config));
            $count++;
        }

        return $count;
    }

    /**
     * 确保配置分类存在，不存在则创建
     * @param string $engTitle 英文标识
     * @param array $data 分类数据
     * @return array ['id' => int, 'created' => bool]
     */
    private function ensureConfigTab(string $engTitle, array $data): array
    {
        $existing = Db::table($this->table('system_config_tab'))
            ->where('eng_title', $engTitle)
            ->find();
        if ($existing) {
            return ['id' => (int)$existing['id'], 'created' => false];
        }
        $id = Db::table($this->table('system_config_tab'))->insertGetId($data);
        return ['id' => (int)$id, 'created' => true];
    }

    /**
     * 清理业务数据（卸载时调用）
     *
     * 清理：
     * - H5 相关 system_config 配置项
     * - 本安装器创建的 h5 配置分类
     */
    protected function cleanData(): int
    {
        $count = 0;

        // 清理 H5 相关 system_config 配置项
        $count += Db::table($this->table('system_config'))
            ->whereIn('menu_name', self::H5_CONFIG_KEYS)
            ->delete();

        // 删除 H5配置 tab
        $count += Db::table($this->table('system_config_tab'))
            ->where('eng_title', 'h5')
            ->delete();

        return $count;
    }
}
