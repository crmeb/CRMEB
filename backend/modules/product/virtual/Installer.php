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

namespace modules\product\virtual;

use crmeb\module\BaseModuleInstaller;

/**
 * 网盘产品类型安装器
 */
class Installer extends BaseModuleInstaller
{
    public function getKey(): string  { return 'product_virtual'; }
    public function getName(): string { return '网盘产品'; }
    public function getType(): string { return 'product_type'; }

    protected function runMigrate(): void
    {
        if (!$this->hasTable('pt_virtual')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_virtual')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `product_id` int NOT NULL DEFAULT 0 COMMENT '关联product.id',
                `download_url` varchar(500) NOT NULL DEFAULT '' COMMENT '下载链接',
                `download_password` varchar(64) NOT NULL DEFAULT '' COMMENT '下载密码',
                `is_auto_send` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否自动发货',
                `send_content` text COMMENT '自动发货内容',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='网盘商品扩展表';");
        }

        if (!$this->hasTable('pt_virtual_order')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_virtual_order')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '关联eb_order_center.order_no',
                `product_id` int NOT NULL DEFAULT 0 COMMENT '商品ID',
                `status` varchar(32) NOT NULL DEFAULT 'pending_payment' COMMENT '业务状态',
                `send_time` int NOT NULL DEFAULT 0 COMMENT '发货时间',
                `complete_time` int NOT NULL DEFAULT 0 COMMENT '完成时间',
                `refund_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '退款状态',
                `add_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `idx_order_no` (`order_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='网盘商品订单表';");
        }

        if (!$this->hasTable('pt_virtual_sku')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_virtual_sku')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `product_id` int NOT NULL DEFAULT 0 COMMENT '关联product.id',
                `sku_name` varchar(200) NOT NULL DEFAULT '' COMMENT 'SKU名称(规格值逗号拼接)',
                `sku_attrs` text COMMENT '规格映射JSON(规格名=>规格值)',
                `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '价格',
                `ot_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '划线价',
                `cost` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '成本价',
                `stock` int NOT NULL DEFAULT 0 COMMENT '库存',
                `sales` int NOT NULL DEFAULT 0 COMMENT '销量',
                `image` varchar(255) NOT NULL DEFAULT '' COMMENT '规格图',
                `download_url` varchar(500) NOT NULL DEFAULT '' COMMENT '网盘链接',
                `download_password` varchar(100) NOT NULL DEFAULT '' COMMENT '提取码',
                `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认',
                `add_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `idx_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='网盘商品SKU表';");
        }
    }

    protected function rollbackMigrate(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_virtual_order')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_virtual_sku')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_virtual')}`;");
    }

    protected function getMenuConfig(): array
    {
        return [
            // 父菜单：网盘产品（挂在"商品"下）
            [
                'parent_key'    => 'core:admin-product',
                'menu_name'     => '网盘产品',
                'controller'    => 'virtual',
                'action'        => 'index',
                'module'        => 'admin',
                'api_url'       => '',
                'menu_path'     => '/product/virtual',
                'unique_auth'   => 'admin-virtual-product',
                'mark'          => '网盘产品管理',
                'icon'          => 'CRMEB-672-yunpan-xianxing',
                // 商品菜单按 sort 倒序展示，实物商品为 10，网盘产品置于其后。
                'sort'          => 9,
                'is_show'       => 1,
                'is_show_path'  => 1,
                'access'        => 1,
                'auth_type'     => 1,
            ],
            // 商品管理
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '商品管理',
                'controller'    => 'virtualProduct',
                'action'        => 'index',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/list',
                'menu_path'     => '/product/virtual/list',
                'unique_auth'   => 'admin-virtual-product-list',
                'mark'          => '网盘商品列表',
                'icon'          => '',
                'sort'          => 1,
                'is_show'       => 1,
                'is_show_path'  => 1,
                'access'        => 1,
                'auth_type'     => 1,
            ],
            // 网盘订单（挂在"订单"下，紧随实物订单）
            [
                'key'           => 'virtual-order-list',
                'parent_key'    => 'core:admin-order',
                'menu_name'     => '网盘订单',
                'controller'    => 'virtualOrder',
                'action'        => 'index',
                'module'        => 'admin',
                'api_url'       => 'virtual/order/list',
                'menu_path'     => '/product/virtual/order/list',
                'unique_auth'   => 'admin-virtual-order-list',
                'mark'          => '网盘订单列表',
                'icon'          => 'CRMEB-102-dingdan-xianxing',
                // 订单菜单按 sort 倒序展示，实物订单为 100，网盘订单紧随其后。
                'sort'          => 90,
                'is_show'       => 1,
                'is_show_path'  => 1,
                'access'        => 1,
                'auth_type'     => 1,
            ],
            // ============ 隐藏权限菜单 ============
            // 创建网盘商品（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '创建网盘商品',
                'controller'    => 'virtualProduct',
                'action'        => 'save',
                'module'        => 'admin',
                'api_url'       => 'virtual/product',
                'methods'       => 'POST',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 更新网盘商品（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '更新网盘商品',
                'controller'    => 'virtualProduct',
                'action'        => 'update',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/:id',
                'methods'       => 'PUT',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 删除网盘商品（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '删除网盘商品',
                'controller'    => 'virtualProduct',
                'action'        => 'delete',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/:id',
                'methods'       => 'DELETE',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 网盘商品详情（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '网盘商品详情',
                'controller'    => 'virtualProduct',
                'action'        => 'read',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/:id',
                'methods'       => 'GET',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 上下架网盘商品（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '上下架网盘商品',
                'controller'    => 'virtualProduct',
                'action'        => 'setStatus',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/status/:id/:status',
                'methods'       => 'PUT',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 恢复网盘商品（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '恢复网盘商品',
                'controller'    => 'virtualProduct',
                'action'        => 'restore',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/restore/:id',
                'methods'       => 'PUT',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 彻底删除网盘商品（隐藏，仅权限认证用）
            [
                'pid'           => '@网盘产品',
                'menu_name'     => '彻底删除网盘商品',
                'controller'    => 'virtualProduct',
                'action'        => 'forceDelete',
                'module'        => 'admin',
                'api_url'       => 'virtual/product/force/:id',
                'methods'       => 'DELETE',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
            // 网盘商品订单详情（隐藏，仅权限认证用）
            [
                'key'           => 'virtual-order-detail',
                'pid'           => '@网盘订单',
                'menu_name'     => '网盘商品订单详情',
                'controller'    => 'virtualOrder',
                'action'        => 'read',
                'module'        => 'admin',
                'api_url'       => 'virtual/order/:order_no',
                'methods'       => 'GET',
                'is_show' => 1,
                'access'        => 1,
                'auth_type'     => 3,
            ],
        ];
    }

    protected function runCustom(): void
    {
        $db = \think\facade\Db::table($this->table('system_menus'));

        // 迁移：清理旧的单一菜单条目（menu_name=虚拟产品, controller=virtualProduct）
        // 这些旧条目的 pid=1，没有子菜单，是早期版本残留
        $oldMenu = (clone $db)
            ->alias('main')
            ->where('main.menu_name', '虚拟产品')
            ->where('main.controller', 'virtualProduct')
            ->whereNotExists(function ($query) {
                $query->table($this->table('system_menus'))
                    ->whereRaw('pid = main.id');
            })
            ->find();
        if ($oldMenu) {
            (clone $db)->delete($oldMenu['id']);
        }

        // 菜单迁移必须在插入新菜单前完成，避免旧版"订单管理"被重复插入。
        $this->migrateMenuStructure();

        // 修复历史存量网盘菜单缺少 menu_path，避免前端子菜单索引冲突。
        (clone $db)
            ->where('menu_name', '网盘产品')
            ->where('controller', 'virtual')
            ->where('menu_path', '')
            ->update(['menu_path' => '/product/virtual']);

        // 升级迁移：SKU 表新增网盘信息列（兼容已安装模块的存量表，幂等）
        $this->ensureColumn('pt_virtual_sku', 'download_url', "`download_url` varchar(500) NOT NULL DEFAULT '' COMMENT '网盘链接' AFTER `sales`");
        $this->ensureColumn('pt_virtual_sku', 'download_password', "`download_password` varchar(100) NOT NULL DEFAULT '' COMMENT '提取码' AFTER `download_url`");
        $this->ensureColumn('pt_virtual_sku', 'sku_attrs', "`sku_attrs` text COMMENT '规格映射JSON(规格名=>规格值)' AFTER `sku_name`");
        $this->ensureColumn('pt_virtual_sku', 'ot_price', "`ot_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '划线价' AFTER `price`");
        $this->ensureColumn('pt_virtual_sku', 'cost', "`cost` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '成本价' AFTER `ot_price`");
        $this->ensureColumn('pt_virtual_sku', 'image', "`image` varchar(255) NOT NULL DEFAULT '' COMMENT '规格图' AFTER `sales`");

        // 存量数据回填：商品级网盘链接 → 默认 SKU（商品级字段废弃后仅 SKU 级生效）
        $virtualRows = \think\facade\Db::table($this->table('pt_virtual'))
            ->where('download_url', '<>', '')
            ->select()
            ->toArray();
        foreach ($virtualRows as $row) {
            \think\facade\Db::table($this->table('pt_virtual_sku'))
                ->where('product_id', (int)($row['product_id'] ?? 0))
                ->where('is_default', 1)
                ->where('download_url', '')
                ->update([
                    'download_url'      => $row['download_url'],
                    'download_password' => $row['download_password'] ?? '',
                ]);
        }

        $this->deployFrontend();
    }

    /**
     * 检查并新增列（存量表升级，幂等）
     * @param string $table 表名（不含前缀）
     * @param string $column 列名
     * @param string $definition 完整列定义（含类型与 COMMENT）
     * @return void
     */
    protected function ensureColumn(string $table, string $column, string $definition): void
    {
        $database = config('database.connections.mysql.database');
        $result = \think\facade\Db::query(
            'SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
            [$database, $this->table($table), $column]
        );
        if (empty($result)) {
            $this->exec("ALTER TABLE `{$this->table($table)}` ADD COLUMN {$definition}");
        }
    }

    /**
     * 升级改名：将存量菜单名中的"虚拟"统一替换为"网盘"
     * 兼容已安装模块的历史菜单数据（menu_name/pid 引用均为旧名称），
     * 改名后新插入的菜单才能按 menu_name 幂等匹配，避免重复插入
     */
    protected function renameMenus(): void
    {
        \think\facade\Db::table($this->table('system_menus'))
            ->where('menu_name', 'like', '%虚拟%')
            ->where(function ($query) {
                $query->where('controller', 'like', 'virtual%')
                    ->whereOr('api_url', 'like', 'virtual/%');
            })
            ->update(['menu_name' => \think\facade\Db::raw("REPLACE(menu_name, '虚拟', '网盘')")]);
    }

    /**
     * 迁移网盘菜单结构，兼容已安装模块的历史菜单记录。
     *
     * 网盘订单统一挂在“订单”一级菜单下，并紧随实物订单；
     * 统计看板已删除，同时清理旧版本遗留的菜单记录。
     */
    protected function migrateMenuStructure(): void
    {
        $this->renameMenus();
        $this->removeStatisticsMenu();
        $this->migrateOrderMenu();
    }

    /**
     * 删除已废弃的网盘统计看板菜单。
     */
    protected function removeStatisticsMenu(): void
    {
        $query = \think\facade\Db::table($this->table('system_menus'))
            ->where('controller', 'virtualStatistics')
            ->where('action', 'index')
            ->where('unique_auth', 'admin-virtual-statistics')
            ->where('auth_type', 1);
        if ($this->supportsOwnershipColumn('system_menus')) {
            $query->whereIn('owner_plugin_id', ['', $this->getOwnerPluginId()]);
        }
        $query->delete();
    }

    /**
     * 将历史“订单管理”菜单迁移为“网盘订单”，并恢复到订单一级菜单下。
     */
    protected function migrateOrderMenu(): void
    {
        $query = \think\facade\Db::table($this->table('system_menus'))
            ->whereIn('menu_name', ['订单管理', '网盘订单'])
            ->where('controller', 'virtualOrder')
            ->where('action', 'index')
            ->where('api_url', 'virtual/order/list')
            ->where('auth_type', 1);
        if ($this->supportsOwnershipColumn('system_menus')) {
            $query->whereIn('owner_plugin_id', ['', $this->getOwnerPluginId()]);
        }
        $menu = $query->order('id', 'asc')->find();
        if (!$menu) {
            return;
        }

        (clone \think\facade\Db::table($this->table('system_menus')))
            ->where('id', $menu['id'])
            ->update([
                'pid'         => $this->getMenuPidByKey('core:admin-order'),
                'menu_name'   => '网盘订单',
                'icon'        => 'CRMEB-102-dingdan-xianxing',
                'sort'        => 90,
                'menu_path'   => '/product/virtual/order/list',
                'unique_auth' => 'admin-virtual-order-list',
            ]);
    }

    /**
     * 重写安装方法：在标准安装流程后执行自定义操作（部署前端文件）
     * 如果模块已安装，也尝试部署前端文件（用于修复未部署的情况）
     */
    public function install(): array
    {
        // 先迁移存量菜单，再由基类按新声明幂等插入/更新。
        $this->migrateMenuStructure();
        $result = parent::install();
        if ($result['status'] === 'success' || $result['status'] === 'skipped') {
            $this->runCustom();
        }
        return $result;
    }

    /**
     * 启用插件：先迁移存量菜单，再重新插入菜单 + 部署前端文件
     */
    public function onEnable(): void
    {
        // 先迁移存量菜单，保证 insertMenus 按新结构幂等匹配，不产生重复菜单。
        $this->migrateMenuStructure();
        parent::onEnable();
        $this->deployFrontend();
    }

    /**
     * 禁用插件：隐藏菜单
     */
    public function onDisable(): void
    {
        parent::onDisable();
    }

    /**
     * 重写卸载方法：先执行自定义回滚（删除前端文件），再执行标准卸载
     */
    public function uninstall(): array
    {
        // 清理未再声明的统计菜单，保证旧版本菜单不会残留。
        $this->removeStatisticsMenu();
        $this->rollbackCustom();
        return parent::uninstall();
    }

    protected function rollbackCustom(): void
    {
        $this->removeFrontend();
    }

    protected function deployFrontend(): void
    {
        $source = __DIR__ . '/frontend/dist/';
        $target = public_path('admin/static/extensions/virtual/');
        if (is_dir($source)) {
            is_dir($target) || mkdir($target, 0755, true);
            $this->recurseCopy($source, $target);
        }
    }

    protected function removeFrontend(): void
    {
        $target = public_path('admin/static/extensions/virtual/');
        if (is_dir($target)) {
            $this->recurseRemove($target);
        }
    }

    protected function recurseCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') continue;
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                is_dir($dstPath) || mkdir($dstPath, 0755, true);
                $this->recurseCopy($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    protected function recurseRemove(string $dir): void
    {
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->recurseRemove($path) : unlink($path);
        }
        rmdir($dir);
    }

    protected function cleanData(): int
    {
        $count = 0;
        $count += \think\facade\Db::table($this->table('pt_virtual_order'))->whereRaw('1=1')->delete();
        $count += \think\facade\Db::table($this->table('pt_virtual_sku'))->whereRaw('1=1')->delete();
        $count += \think\facade\Db::table($this->table('pt_virtual'))->whereRaw('1=1')->delete();
        return $count;
    }
}
