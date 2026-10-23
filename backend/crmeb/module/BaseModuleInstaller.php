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

namespace crmeb\module;

use crmeb\interfaces\ModuleInstallerInterface;
use crmeb\plugin\PluginManifest;
use crmeb\services\CacheService;
use think\facade\Db;

/**
 * 模块安装器基类
 * 封装建表、菜单、种子的通用逻辑，子类只需实现抽象方法
 *
 * 本类位于 crmeb 核心层，是模块生命周期基础设施（DDL/DML/菜单管理），
 * 供 modules/ 与 driver/ 各 Installer 继承使用，符合三层架构依赖铁律
 * （driver 层不可调用 app 层，故基类必须下沉到 crmeb 层）。
 *
 * Db::table/Db::query/Db::execute 用于建表、菜单管理等 DDL/DML 操作，
 * 属于 Installer 生命周期范畴，保留直接调用。
 */
abstract class BaseModuleInstaller implements ModuleInstallerInterface
{
    /**
     * 核心共享表黑名单（cleanTable 拒绝清理）
     *
     * 这些表为全站共享数据（菜单/配置/权限/用户/中台商品与订单/页面链接/模块基础设施），
     * 不归属任何单一模块。子类 Installer 传这些表名一律拒绝，
     * 防止二开插件误传表名或恶意清空核心数据。
     * 模块自建表（如 file_manage 的 system_file / system_file_info）不在名单内。
     */
    protected const CORE_SHARED_TABLES = [
        // 菜单 / 配置 / 权限
        'system_menus', 'system_config', 'system_config_tab', 'system_admin', 'system_role',
        // 用户
        'user', 'user_bill', 'user_group',
        // 中台商品 / 订单
        'product_center', 'order_center', 'order_item', 'store_cart',
        // DIY 页面链接
        'page_categroy', 'page_link',
        // 模块基础设施
        'module_install_log', 'system_file_md5',
        // 插件间共享表准入登记表（协议基础设施，插件禁碰）
        'shared_table_registry',
    ];

    /**
     * 插件间共享表准入目录（白名单）
     *
     * 与 CORE_SHARED_TABLES（app 层独占、插件禁碰）不同，目录内的表允许多个插件
     * 共用：各插件通过 sharedTables() 声明准入，安装/启用时登记到
     * eb_shared_table_registry，卸载时释放；DROP 必须走 dropSharedTableIfUnclaimed()
     * 仲裁，仅当无任何插件占用时才允许删除。
     *
     * 多插件共用的表必须先入目录才允许声明共享；目录外表一律视为插件自建表，
     * 由建表插件全权负责。键为表名（不含前缀），值为说明。
     */
    protected const SHARED_TABLE_CATALOG = [
        // 活动通用表：type 区分活动类型（1=秒杀），由秒杀插件负责建表
        'store_activity' => '活动通用表（type 区分活动类型，多活动插件共用）',
    ];

    protected ?string $ownerPluginId = null;

    /** @var array<string, bool> */
    protected array $ownershipColumnCache = [];

    /** @var bool|null */
    protected ?bool $menuKeyColumnSupported = null;

    /**
     * 数据库表前缀
     * @var string
     */
    protected string $prefix;

    /**
     * 字符集
     * @var string
     */
    protected string $charset;

    /**
     * 表排序规则
     * @var string
     */
    protected string $collation;

    public function __construct()
    {
        $this->prefix    = (string) config('database.connections.mysql.prefix', 'eb_');
        $this->charset   = (string) config('database.connections.mysql.charset', 'utf8mb4');
        $this->collation = $this->charset === 'utf8mb4' ? 'utf8mb4_general_ci' : 'utf8_general_ci';
    }

    // ============== 安装流程 ==============

    /**
     * 安装
     *
     * 注意：runMigrate() 包含 CREATE TABLE 等 DDL 语句，MySQL 会在执行 DDL 时
     * 隐式提交当前事务。因此本方法将 DDL 放在事务外执行，业务数据操作（菜单、
     * 种子、安装日志）放在事务内执行，避免 commit/rollback 异常。
     *
     * @return array
     */
    public function install(): array
    {
        if ($this->isInstalled()) {
            return ['status' => 'skipped', 'message' => '模块已安装，跳过'];
        }

        $steps = [];

        // 1. 前置检查
        $check = $this->checkRequirements();
        if (!empty($check['errors'])) {
            throw new \RuntimeException('前置检查失败: ' . implode('; ', $check['errors']));
        }

        // 2. 建表（DDL，必须在事务外执行）
        $this->runMigrate();
        $this->ensureSharedOwnershipColumns();
        $steps[] = '数据库表创建完成';

        // 3. 插入菜单、model_checkbox 配置、种子、安装日志等业务数据（在事务内执行）
        $ownershipWatermarks = $this->captureSharedResourceWatermarks();
        Db::startTrans();
        try {
            $menuCount = $this->insertMenus();
            $steps[] = "插入 {$menuCount} 个后台菜单";

            // 将模块加入 model_checkbox 显示配置，确保后台菜单不会被过滤掉
            $this->addToModelCheckbox();

            $seedCount = $this->seed();
            if ($seedCount > 0) {
                $steps[] = "写入 {$seedCount} 条初始数据";
            }

            // 共享表准入登记（uk_table_plugin 幂等，失败随事务回滚）
            $this->claimSharedTables();

            $this->claimSharedResources($ownershipWatermarks);

            if ($this->installDriverRecord()) {
                $steps[] = '写入驱动字典';
            }

            $this->logInstall();

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        return ['status' => 'success', 'steps' => $steps];
    }

    // ============== 卸载流程 ==============

    /**
     * 卸载
     *
     * 即使插件已被标记为未安装（status=0），也会执行菜单清理，
     * 防止旧版遗留菜单未被删除的情况。
     *
     * 注意：rollbackMigrate() 包含 DROP TABLE 等 DDL 语句，MySQL 会在执行 DDL 时
     * 隐式提交当前事务。因此本方法将 DDL 放在事务外执行，业务数据操作（清理数据、
     * 移除菜单、卸载日志）放在事务内执行，避免 commit/rollback 异常。
     *
     * @return array
     */
    public function uninstall(): array
    {
        // 旧版本数据库可能没有公共资源归属字段，必须在事务外先补齐。
        $this->ensureSharedOwnershipColumns();
        $isInstalled = $this->isInstalled();

        if (!$isInstalled) {
            $menuCount = $this->removeMenus();
            $this->removeFromModelCheckbox();
            if ($menuCount > 0) {
                return ['status' => 'success', 'steps' => ["清理 {$menuCount} 个残留菜单"]];
            }
            return ['status' => 'skipped', 'message' => '模块未安装，跳过'];
        }

        $steps = [];

        // 1. 清理业务数据、移除菜单、记录卸载日志（在事务内执行）
        Db::startTrans();
        try {
            $cleanCount = $this->cleanData();
            if ($cleanCount > 0) {
                $steps[] = "清理 {$cleanCount} 条业务数据";
            }

            $menuCount = $this->removeMenus();
            $steps[] = "移除 {$menuCount} 个后台菜单";

            // 释放共享表准入登记（先于 rollbackMigrate 仲裁，失败随事务回滚）
            $this->releaseSharedTables();

            $ownedCount = $this->removeOwnedSharedResources();
            if ($ownedCount > 0) {
                $steps[] = "清理 {$ownedCount} 条插件所属公共资源";
            }

            if ($this->removeDriverRecord()) {
                $steps[] = '删除驱动字典记录';
            }

            $this->logUninstall();

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        // 2. 从 model_checkbox 显示配置中移除该模块（事务外）
        $this->removeFromModelCheckbox();

        // 3. 删除表（DDL，必须在事务外执行）
        $this->rollbackMigrate();
        $steps[] = '数据库表删除完成';

        // 4. 备份插件文件到 backup/ 目录（延迟执行，避免自移异常）
        // $this->backupPluginFiles();
        // $steps[] = '插件文件已备份';

        return ['status' => 'success', 'steps' => $steps];
    }

    /**
     * 是否已安装
     * @return bool
     */
    public function isInstalled(): bool
    {
        return Db::table($this->prefix . 'module_install_log')
            ->where('module_key', $this->getKey())
            ->where('status', 1)
            ->count() > 0;
    }

    /**
     * 安装前置检查
     * @return array
     */
    public function checkRequirements(): array
    {
        return ['errors' => []];
    }

    // ============== 子类实现的抽象方法 ==============

    /**
     * 建表
     * @return void
     */
    abstract protected function runMigrate(): void;

    /**
     * 回滚建表
     * @return void
     */
    abstract protected function rollbackMigrate(): void;

    /**
     * 获取菜单配置
     * @return array
     */
    abstract protected function getMenuConfig(): array;

    // ============== 子类可选重写 ==============

    /**
     * 获取本插件的消息通知声明；无消息时保持空数组。
     * @return array
     */
    protected function getNoticeDefinitions(): array
    {
        return [];
    }

    /**
     * [TEMPORARY] 导出通知种子定义（归属标识 + getNoticeDefinitions 结果）。
     *
     * TEMPORARY-PLUGIN-NOTICE-REFRESH：临时功能——供插件列表"刷新消息模板"按钮经
     * app 层 ModuleInstaller::refreshNotices() 调用，以代码定义覆盖式更新消息模板，
     * 后续随该按钮一并移除。仅暴露受保护成员，不含任何业务逻辑。
     *
     * @return array{owner: string, definitions: array}
     */
    public function exportNoticeSeed(): array
    {
        return [
            'owner' => $this->getOwnerPluginId(),
            'definitions' => $this->getNoticeDefinitions(),
        ];
    }

    /**
     * 获取本插件的通知渠道声明；无渠道时保持空数组。
     * @return array
     */
    protected function getNoticeChannelDefinitions(): array
    {
        return [];
    }

    /**
     * 种子数据
     * @return int
     */
    protected function seed(): int
    {
        return 0;
    }

    /**
     * 清理业务数据
     * @return int
     */
    protected function cleanData(): int
    {
        return 0;
    }

    /**
     * 启用时回调
     * 默认实现：重新插入菜单、将模块加入 model_checkbox 显示配置，
     * 并幂等补跑 seed()——插件升级新增种子数据（如 DIY 页面链接）时，
     * 无需重装，执行一次禁用/启用即可落库
     * 子类可重写以部署驱动文件等，需调用 parent::onEnable()
     * @return void
     */
    public function onEnable(): void
    {
        // 旧站点可能直接升级代码后执行启用，先在事务外补齐菜单归属与稳定标识。
        $this->ensureSharedOwnershipColumns();
        $this->insertMenus();
        $this->addToModelCheckbox();
        $this->seed();
        // 存量站点禁用→启用一次即可补登共享表准入（幂等）
        $this->claimSharedTables();
    }

    /**
     * 禁用时回调
     * 默认实现：隐藏菜单（is_show=0, is_show_path=0），保留数据库记录；
     *          并将模块从 model_checkbox 显示配置中移除
     * 子类可重写以移除驱动文件等，需调用 parent::onDisable()
     * @return void
     */
    public function onDisable(): void
    {
        $this->ensureSharedOwnershipColumns();
        $this->hideMenus();
        $this->removeFromModelCheckbox();
    }

    // ============== 插件间共享表准入协议 ==============

    /**
     * 子类声明本插件使用的插件间共享表（准入声明）
     *
     * 返回 SHARED_TABLE_CATALOG 内的表名列表；声明后 install()/onEnable() 会自动
     * 登记，uninstall() 自动释放。目录外表声明即抛异常，禁止私自把自建表包装成共享表。
     * @return array 表名列表（不含前缀），默认空
     */
    protected function sharedTables(): array
    {
        return [];
    }

    /**
     * 登记共享表准入（install/onEnable 时调用，幂等）
     *
     * 校验声明的表必须在准入目录内，按 (table_name, owner_plugin_id) 唯一键
     * ON DUPLICATE KEY UPDATE 幂等写入；存量站点可反复执行补登。
     * @return void
     */
    protected function claimSharedTables(): void
    {
        $tables = $this->sharedTables();
        if (empty($tables)) {
            return;
        }

        $this->ensureSharedTableRegistry();

        $owner = $this->getOwnerPluginId();
        $now   = time();
        foreach ($tables as $name) {
            $name = (string) $name;

            // 准入校验：目录外表拒绝登记
            if (!isset(self::SHARED_TABLE_CATALOG[$name])) {
                throw new \RuntimeException(
                    "sharedTables() 声明的表 {$name} 不在共享表准入目录（BaseModuleInstaller::SHARED_TABLE_CATALOG），禁止共享"
                );
            }

            Db::table($this->table('shared_table_registry'))->duplicate([
                'add_time' => $now,
            ])->insert([
                'table_name'      => $name,
                'owner_plugin_id' => $owner,
                'add_time'        => $now,
            ]);
        }
    }

    /**
     * 释放本插件全部共享表准入登记（uninstall 时调用）
     * @return int 删除登记条数
     */
    protected function releaseSharedTables(): int
    {
        if (!$this->hasTable('shared_table_registry')) {
            return 0;
        }
        return (int) Db::table($this->table('shared_table_registry'))
            ->where('owner_plugin_id', $this->getOwnerPluginId())
            ->delete();
    }

    /**
     * 共享表仲裁式删除（rollbackMigrate 中替代裸 DROP）
     *
     * 仲裁规则：先释放本插件登记，登记表中仍有其他插件占用该表则拒绝删除；
     * 无任何占用者时才执行 DROP。目录外表禁止通过本方法删除。
     * @param string $name 共享表名（不含前缀）
     * @return bool 是否实际执行了 DROP
     */
    protected function dropSharedTableIfUnclaimed(string $name): bool
    {
        // 仅准入目录内的表允许仲裁删除
        if (!isset(self::SHARED_TABLE_CATALOG[$name])) {
            throw new \RuntimeException(
                "dropSharedTableIfUnclaimed() 仅允许仲裁共享表准入目录内的表：{$name} 不在目录中"
            );
        }

        // 幂等：表不存在（旧版已删或从未建表）直接跳过
        if (!$this->hasTable($name)) {
            return false;
        }

        // 释放本插件登记后，仍有其他插件占用则保守保留共享表
        $this->releaseSharedTables();
        if ($this->hasTable('shared_table_registry')
            && Db::table($this->table('shared_table_registry'))
                ->where('table_name', $name)
                ->count() > 0) {
            return false;
        }

        $this->exec("DROP TABLE IF EXISTS `{$this->table($name)}`;");
        return true;
    }

    /**
     * 幂等创建共享表准入登记表（存量站点兜底，DDL 必须在事务外执行）
     * 新安装库由 crmeb_table.sql 创建；旧库升级时在此补齐。
     * @return void
     */
    protected function ensureSharedTableRegistry(): void
    {
        if ($this->hasTable('shared_table_registry')) {
            return;
        }
        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('shared_table_registry')}` (
            `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
            `table_name` varchar(64) NOT NULL DEFAULT '' COMMENT '共享表名（不含前缀）',
            `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '占用插件复合标识，如 module:trade:seckill',
            `add_time` int NOT NULL DEFAULT 0 COMMENT '登记时间',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_table_plugin` (`table_name`,`owner_plugin_id`),
            KEY `idx_table_name` (`table_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='插件间共享表准入登记表';");
    }

    // ============== 通用工具方法 ==============

    /**
     * 获取完整表名
     * @param string $name
     * @return string
     */
    protected function table(string $name): string
    {
        return $this->prefix . $name;
    }

    /**
     * 判断表是否存在
     * @param string $name
     * @return bool
     */
    protected function hasTable(string $name): bool
    {
        $database = config('database.connections.mysql.database');
        $sql      = "SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1";
        $result   = Db::query($sql, [$database, $this->table($name)]);
        return !empty($result);
    }

    /**
     * 执行建表 SQL
     * @param string $sql
     * @return void
     */
    protected function exec(string $sql): void
    {
        // 显式设置客户端连接字符集，防止 COMMENT 中的中文因连接字符集不匹配而乱码
        Db::execute("SET NAMES '{$this->charset}'");
        Db::execute($sql);
    }

    /**
     * 插入或更新插件菜单
     *
     * 新库按 menu_key 幂等写入；旧库或历史无归属菜单先按旧菜单身份匹配，
     * 命中后认领为本插件资源并补齐 menu_key。已存在菜单会同步声明属性，
     * 因此插件升级修改路由、父级、排序后无需先卸载。
     *
     * @return int 实际新增或更新的菜单数
     */
    protected function insertMenus(): int
    {
        $menus = array_map(fn (array $menu): array => $this->normalizeMenu($menu), $this->getMenuConfig());
        $count = 0;
        $nameToId = [];
        $menuKeys = [];

        // 同一插件内菜单稳定标识必须唯一，冲突时在写库前直接失败。
        foreach ($menus as $menu) {
            $menuKey = $menu['menu_key'];
            if (isset($menuKeys[$menuKey])) {
                throw new \RuntimeException(
                    "插件 {$this->getOwnerPluginId()} 存在重复菜单标识 {$menuKey}："
                    . "{$menuKeys[$menuKey]} / {$menu['menu_name']}"
                );
            }
            $menuKeys[$menuKey] = $menu['menu_name'];
        }

        foreach ($menus as $menu) {
            $menu['pid'] = $this->resolveMenuParentId($menu, $nameToId);

            $menuName = $menu['menu_name'];

            // 新数据按稳定键查找；存量数据再按旧身份兼容认领。
            $existing = (clone $this->menuQuery($menu))->find();
            if (!$existing && $this->supportsMenuKeyColumn()) {
                $legacyQuery = clone $this->legacyMenuQuery($menu);
                if ($this->supportsOwnershipColumn('system_menus')) {
                    $legacyQuery->whereIn('owner_plugin_id', ['', $this->getOwnerPluginId()]);
                }
                $existing = $legacyQuery->find();
            }

            if ($existing) {
                $updates = $this->getMenuUpdates($existing, $menu);
                if (!empty($updates)) {
                    Db::table($this->prefix . 'system_menus')
                        ->where('id', $existing['id'])
                        ->update($updates);
                    $count++;
                }
                $nameToId[$menuName] = (int) $existing['id'];
                continue;
            }

            if ($this->supportsOwnershipColumn('system_menus')) {
                $menu['owner_plugin_id'] = $this->getOwnerPluginId();
            }

            // 只保留表中存在的字段，避免 insert 报错
            static $allowedFields = [
                'pid','icon','menu_name','module','controller','action',
                'api_url','methods','params','sort','is_show','is_show_path',
                'access','menu_path','path','auth_type','header','is_header',
                'unique_auth','menu_key','is_del','mark','owner_plugin_id',
            ];
            if (!$this->supportsMenuKeyColumn()) {
                unset($menu['menu_key']);
            }
            unset($menu['key'], $menu['parent_key']);
            $menu = array_intersect_key($menu, array_fill_keys($allowedFields, true));

            $id = (int) Db::table($this->prefix . 'system_menus')->insertGetId($menu);
            $nameToId[$menuName] = $id;
            $count++;
        }

        return $count;
    }

    /**
     * 补齐菜单默认值并生成本插件不可变的稳定标识。
     *
     * 可通过 key 显式声明可读的插件内局部标识；未声明时根据权限类型、
     * 权限码、接口、控制器动作和声明名称生成稳定哈希。menu_path 不参与哈希，
     * 避免动态配置分类 ID 回填后菜单身份发生变化。
     *
     * @param array $menu 菜单声明
     * @return array
     */
    protected function normalizeMenu(array $menu): array
    {
        $menu = array_merge([
            'pid'           => 0,
            'menu_name'     => '',
            'controller'    => '',
            'action'        => '',
            'module'        => 'admin',
            'api_url'       => '',
            'methods'       => '',
            'params'        => '[]',
            'menu_path'     => '',
            'path'          => '',
            'unique_auth'   => '',
            'header'        => '',
            'is_header'     => 0,
            'mark'          => '',
            'icon'          => '',
            'sort'          => 0,
            'is_show'       => 1,
            'is_show_path'  => 1,
            'access'        => 1,
            'auth_type'     => 1,
            'is_del'        => 0,
        ], $menu);

        if ($menu['menu_name'] === '') {
            throw new \RuntimeException("插件 {$this->getOwnerPluginId()} 的菜单声明缺少 menu_name");
        }
        if (is_array($menu['params'])) {
            $menu['params'] = json_encode($menu['params'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        $localKey = trim((string)($menu['key'] ?? ''));
        if ($localKey !== '') {
            if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/', $localKey)) {
                throw new \RuntimeException("插件菜单 key 非法：{$localKey}，仅允许小写字母、数字、点、下划线和短横线");
            }
            $menu['menu_key'] = $this->getOwnerPluginId() . ':menu:' . $localKey;
        } else {
            $identity = [
                (string)(int)$menu['auth_type'],
                (string)$menu['unique_auth'],
                (string)$menu['api_url'],
                strtoupper((string)$menu['methods']),
                (string)$menu['controller'],
                (string)$menu['action'],
                (string)$menu['menu_name'],
            ];
            $menu['menu_key'] = $this->getOwnerPluginId() . ':menu:' . sha1(implode("\x1F", $identity));
        }

        return $menu;
    }

    /**
     * 解析菜单父级。
     *
     * parent_key 用于引用系统或其他已存在菜单的稳定标识；pid=@菜单名仅保留为
     * 同批次插件菜单的兼容语法。父级不存在时必须中止安装，禁止静默挂到顶级。
     *
     * @param array $menu 菜单声明
     * @param array<string, int> $nameToId 本批次已写入菜单映射
     * @return int
     */
    protected function resolveMenuParentId(array $menu, array $nameToId): int
    {
        $parentKey = trim((string)($menu['parent_key'] ?? ''));
        if ($parentKey !== '') {
            return $this->getMenuPidByKey($parentKey);
        }

        $pid = $menu['pid'] ?? 0;
        if (is_string($pid) && str_starts_with($pid, '@')) {
            $parentName = substr($pid, 1);
            if (!isset($nameToId[$parentName])) {
                throw new \RuntimeException(
                    "插件 {$this->getOwnerPluginId()} 菜单 {$menu['menu_name']} 引用的父菜单 @{$parentName} 尚未写入"
                );
            }
            return $nameToId[$parentName];
        }

        return (int)$pid;
    }

    /**
     * 计算已存在插件菜单需要同步的字段。
     *
     * @param array $existing 数据库菜单
     * @param array $menu 标准化菜单声明
     * @return array
     */
    protected function getMenuUpdates(array $existing, array $menu): array
    {
        $fields = [
            'pid', 'icon', 'menu_name', 'module', 'controller', 'action', 'api_url',
            'methods', 'params', 'sort', 'is_show', 'is_show_path', 'access',
            'menu_path', 'path', 'auth_type', 'header', 'is_header', 'unique_auth',
            'is_del', 'mark',
        ];
        if ($this->supportsMenuKeyColumn()) {
            $fields[] = 'menu_key';
        }
        if ($this->supportsOwnershipColumn('system_menus')) {
            $menu['owner_plugin_id'] = $this->getOwnerPluginId();
            $fields[] = 'owner_plugin_id';
        }

        $updates = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $existing) || (string)$existing[$field] !== (string)$menu[$field]) {
                $updates[$field] = $menu[$field];
            }
        }
        return $updates;
    }

    /**
     * 根据菜单唯一性规则构建查询
     *
     * 菜单类型与唯一键对应关系（与核心权限校验 SystemRoleServices::verifyAuth 一致）：
     * - 菜单（auth_type=1）：menu_name + menu_path
     * - 接口权限（auth_type=2）：menu_name + api_url + methods，verifyAuth 仅识别此类节点管控接口
     * - 操作按钮（auth_type=3）：menu_name + unique_auth，仅做展示层，不参与接口鉴权
     *
     * ⚠️ 模块菜单中带 api_url 的权限项必须声明 auth_type=2，否则接口不会被
     * verifyAuth 纳入管控（未注册即放行）。
     *
     * @param array $menu 菜单配置项
     * @return \think\db\Query
     */
    protected function menuQuery(array $menu): \think\db\Query
    {
        $menu = $this->normalizeMenu($menu);
        if ($this->supportsMenuKeyColumn()) {
            return Db::table($this->prefix . 'system_menus')
                ->where('menu_key', $menu['menu_key']);
        }
        return $this->legacyMenuQuery($menu);
    }

    /**
     * 按历史菜单字段构建兼容查询，仅用于认领/清理升级前的无归属菜单。
     *
     * 菜单类型语义以核心权限校验（SystemRoleServices::verifyAuth）为准：
     * auth_type=1 菜单、auth_type=2 接口权限（api_url+methods，仅此类参与接口鉴权）、
     * auth_type=3 操作按钮（unique_auth，仅展示层）。
     *
     * @param array $menu 菜单配置项
     * @return \think\db\Query
     */
    protected function legacyMenuQuery(array $menu): \think\db\Query
    {
        $query = Db::table($this->prefix . 'system_menus')
            ->where('menu_name', $menu['menu_name'] ?? '');

        $authType = (int)($menu['auth_type'] ?? 1);
        $menuPath = $menu['menu_path'] ?? '';
        $uniqueAuth = $menu['unique_auth'] ?? '';
        $apiUrl = $menu['api_url'] ?? '';
        $methods = $menu['methods'] ?? '';

        $query->where('auth_type', $authType);

        if ($authType === 1 && !empty($menuPath)) {
            // 菜单：按 menu_name + menu_path 唯一
            $query->where('menu_path', $menuPath);
        } elseif ($authType === 2 && !empty($apiUrl)) {
            // 接口权限：按 menu_name + api_url + methods 唯一
            $query->where('api_url', $apiUrl);
            if ($methods !== '') {
                $query->where('methods', $methods);
            }
        } elseif ($authType === 3 && !empty($uniqueAuth)) {
            // 操作按钮：按 menu_name + unique_auth 唯一
            $query->where('unique_auth', $uniqueAuth);
        } elseif (!empty($uniqueAuth)) {
            // 历史节点字段不完整时以权限标识收窄，禁止仅按中文名匹配。
            $query->where('unique_auth', $uniqueAuth);
        } elseif (!empty($apiUrl)) {
            $query->where('api_url', $apiUrl);
            if ($methods !== '') {
                $query->where('methods', $methods);
            }
        } elseif (!empty($menu['controller']) || !empty($menu['action'])) {
            $query->where('controller', $menu['controller'] ?? '')
                ->where('action', $menu['action'] ?? '');
        }

        return $query;
    }

    /**
     * 删除菜单（物理删除，用于卸载时清除菜单记录）
     *
     * 新数据按 owner_plugin_id 清理；升级前的无归属菜单仅按完整历史身份兼容匹配，
     * 不再使用单独 api_url 或 controller/action 的宽泛条件，避免误删核心或其他插件菜单。
     * 收集全部匹配 ID 后一次性物理删除。
     *
     * @return int
     */
    protected function removeMenus(): int
    {
        $menus = $this->getMenuConfig();
        $deleteIds = [];
        $supportsOwnership = $this->supportsOwnershipColumn('system_menus');
        $owner = $this->getOwnerPluginId();

        if ($supportsOwnership) {
            // 新数据直接按插件归属清理，不依赖菜单名称或路由。
            $deleteIds = Db::table($this->prefix . 'system_menus')
                ->where('owner_plugin_id', $owner)
                ->column('id');
        }

        foreach ($menus as $menu) {
            if (!empty($menu['menu_name'])) {
                $query = clone $this->legacyMenuQuery($menu);
                if ($supportsOwnership) {
                    // 仅兼容升级前的无归属菜单，避免碰到其他插件拥有的同路由菜单。
                    $query->where('owner_plugin_id', '');
                }
                $ids = $query->column('id');
                $deleteIds = array_merge($deleteIds, $ids);
            }

        }

        $deleteIds = array_unique($deleteIds);

        if (empty($deleteIds)) {
            return 0;
        }

        return Db::table($this->prefix . 'system_menus')
            ->whereIn('id', $deleteIds)
            ->delete();
    }

    /**
     * 恢复被软删除的菜单（将 is_del 置回 0）
     * 按菜单唯一性规则匹配，避免误恢复其他模块的同名菜单
     * @return int
     */
    protected function restoreMenus(): int
    {
        $menus = $this->getMenuConfig();
        $count = 0;

        foreach ($menus as $menu) {
            $count += (clone $this->menuQuery($menu))->update(['is_del' => 0]);
        }

        return $count;
    }

    /**
     * 隐藏菜单（设置 is_show = 0、is_show_path = 0，用于禁用时隐藏）
     * 注意：is_show_path 必须同步置 0，否则菜单查询（is_show_path = 1）仍会返回该菜单
     * 按菜单唯一性规则匹配，避免误隐藏其他模块的同名菜单
     * @return int
     */
    protected function hideMenus(): int
    {
        $menus = $this->getMenuConfig();
        $count = 0;

        foreach ($menus as $menu) {
            $count += (clone $this->menuQuery($menu))
                ->update(['is_show' => 0, 'is_show_path' => 0]);
        }

        return $count;
    }

    /**
     * 显示菜单（设置 is_show = 1、is_show_path = 1，用于启用时展示）
     * 注意：is_show_path 必须同步置 1，否则菜单查询（is_show_path = 1）无法返回该菜单
     * 按菜单唯一性规则匹配，避免误显示其他模块的同名菜单
     * @return int
     */
    protected function showMenus(): int
    {
        $menus = $this->getMenuConfig();
        $count = 0;

        foreach ($menus as $menu) {
            $count += (clone $this->menuQuery($menu))
                ->update(['is_show' => 1, 'is_show_path' => 1]);
        }

        return $count;
    }

    /**
     * 记录安装日志
     * 安装后默认启用（enabled=1），与前端"启用显示菜单、禁用隐藏菜单"逻辑一致
     * @return void
     */
    protected function logInstall(): void
    {
        Db::table($this->prefix . 'module_install_log')->duplicate([
            'module_name'  => $this->getName(),
            'module_type'  => $this->getType(),
            'version'      => $this->getVersion(),
            'status'       => 1,
            'enabled'      => 1,
            'install_time' => time(),
            'uninstall_time' => 0,
        ])->insert([
            'module_key'   => $this->getKey(),
            'module_name'  => $this->getName(),
            'module_type'  => $this->getType(),
            'version'      => $this->getVersion(),
            'status'       => 1,
            'enabled'      => 1,
            'install_time' => time(),
            'uninstall_time' => 0,
        ]);
    }

    /**
     * 记录卸载日志
     * @return void
     */
    protected function logUninstall(): void
    {
        Db::table($this->prefix . 'module_install_log')
            ->where('module_key', $this->getKey())
            ->where('status', 1)
            ->update([
                'status'          => 0,
                'uninstall_time'  => time(),
            ]);
    }

    /**
     * 模块版本号
     * @return string
     */
    protected function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * 获取 model_checkbox 配置中的模块 key
     *
     * 子类可重写此方法，返回对应模块在 `eb_system_config.model_checkbox`
     * 中的标识，例如 'seckill'、'bargain'、'combination'。
     * 返回空字符串表示该模块不受 model_checkbox 控制。
     *
     * @return string
     */
    protected function getModelCheckboxKey(): string
    {
        return '';
    }

    /**
     * 将模块加入 model_checkbox 显示配置
     *
     * 安装/启用时调用，确保后台菜单不会被 searchNoModelAttr 过滤掉。
     *
     * @return void
     */
    protected function addToModelCheckbox(): void
    {
        $key = $this->getModelCheckboxKey();
        if ($key === '') {
            return;
        }

        $config = Db::table($this->prefix . 'system_config')
            ->where('menu_name', 'model_checkbox')
            ->find();

        if (!$config) {
            return;
        }

        $value = json_decode($config['value'] ?? '[]', true);
        if (!is_array($value)) {
            $value = [];
        }

        if (in_array($key, $value, true)) {
            return;
        }

        $value[] = $key;

        Db::table($this->prefix . 'system_config')
            ->where('menu_name', 'model_checkbox')
            ->update(['value' => json_encode($value, JSON_UNESCAPED_UNICODE)]);

        $this->clearModelCheckboxCache();
    }

    /**
     * 将模块从 model_checkbox 显示配置中移除
     *
     * 卸载/禁用时调用，避免模块已卸载后菜单仍被展示。
     *
     * @return void
     */
    protected function removeFromModelCheckbox(): void
    {
        $key = $this->getModelCheckboxKey();
        if ($key === '') {
            return;
        }

        $config = Db::table($this->prefix . 'system_config')
            ->where('menu_name', 'model_checkbox')
            ->find();

        if (!$config) {
            return;
        }

        $value = json_decode($config['value'] ?? '[]', true);
        if (!is_array($value)) {
            return;
        }

        $value = array_values(array_diff($value, [$key]));

        Db::table($this->prefix . 'system_config')
            ->where('menu_name', 'model_checkbox')
            ->update(['value' => json_encode($value, JSON_UNESCAPED_UNICODE)]);

        $this->clearModelCheckboxCache();
    }

    /**
     * 清除 model_checkbox 配置缓存
     * @return void
     */
    protected function clearModelCheckboxCache(): void
    {
        try {
            CacheService::delete('system_config_model_checkbox');
        } catch (\Throwable $e) {
            // 缓存清除失败不影响主流程
        }
    }

    /**
     * 根据菜单名和 controller 查询父菜单 ID
     * 仅为第三方旧插件保留；新插件必须使用 parent_key/getMenuPidByKey。
     * @param string $menuName 菜单名称
     * @param string $controller 控制器标识
     * @return int
     * @deprecated 使用 getMenuPidByKey()
     */
    protected function getMenuPid(string $menuName, string $controller = ''): int
    {
        $menu = Db::table($this->prefix . 'system_menus')
            ->where('menu_name', $menuName)
            ->where('controller', $controller)
            ->where('is_del', 0)
            ->find();
        return $menu ? (int) $menu['id'] : 0;
    }

    /**
     * 根据不可变菜单标识查询父菜单 ID。
     *
     * core:<unique_auth> 在存量库尚未执行 menu_key 迁移时，会安全回退到
     * unique_auth 查询；仍不存在则抛出异常，避免插件菜单错误挂到根节点。
     *
     * @param string $menuKey 菜单稳定标识
     * @return int
     */
    protected function getMenuPidByKey(string $menuKey): int
    {
        $menu = null;
        if ($this->supportsMenuKeyColumn()) {
            $menu = Db::table($this->prefix . 'system_menus')
                ->where('menu_key', $menuKey)
                ->where('is_del', 0)
                ->find();
        }

        if (!$menu && str_starts_with($menuKey, 'core:')) {
            $uniqueAuth = substr($menuKey, strlen('core:'));
            if ($uniqueAuth !== '' && !str_starts_with($uniqueAuth, 'menu:')) {
                $query = Db::table($this->prefix . 'system_menus')
                    ->where('unique_auth', $uniqueAuth)
                    ->where('is_del', 0);
                if ($this->supportsOwnershipColumn('system_menus')) {
                    $query->where('owner_plugin_id', '');
                }
                $menu = $query->find();
            }
        }

        if (!$menu) {
            throw new \RuntimeException("插件 {$this->getOwnerPluginId()} 依赖的父菜单不存在：{$menuKey}");
        }
        return (int)$menu['id'];
    }

    /**
     * 统计指定类型的已安装模块数量
     * @param string $type 模块类型
     * @return int
     */
    protected function countInstalledByType(string $type): int
    {
        return (int) Db::table($this->prefix . 'module_install_log')
            ->where('module_type', $type)
            ->where('status', 1)
            ->count();
    }

    /**
     * 模块自有表白名单（子类重写声明）
     *
     * cleanTable() 仅允许清理本清单内的表，未声明一律拒绝（deny-by-default）。
     * "模块拥有哪些表"是模块自己的知识：清单应覆盖模块 runMigrate() 建的全部业务表；
     * 共享表仅在模块只按条件清理自身数据时声明（如 store_activity 按 type 区分归属）。
     * @return array 表名列表（不含前缀）
     */
    protected function getOwnedTables(): array
    {
        return [];
    }

    /**
     * 清理指定业务表数据
     * @param string $name 表名（不含前缀），必须在 getOwnedTables() 白名单内，且不得为核心共享表
     * @param array $where 删除条件，空数组则全表清空
     * @return int 删除行数
     */
    protected function cleanTable(string $name, array $where = []): int
    {
        // 第一道防线：核心共享表绝对禁止，防止子类将核心表误声明为自有表
        if (in_array($name, self::CORE_SHARED_TABLES, true)) {
            throw new \Exception("cleanTable() 禁止清理核心共享表：{$name}，模块数据清理仅允许针对模块自建表");
        }
        // 第二道防线：表必须已在模块自有表白名单中声明，未声明一律拒绝
        if (!in_array($name, $this->getOwnedTables(), true)) {
            throw new \Exception("cleanTable() 表 {$name} 未在 getOwnedTables() 中声明为模块自有表，禁止清理");
        }
        // 第三道防线：插件间共享表禁止无条件全表清空，必须携带归属条件（如 type）只清理本插件数据行
        if (isset(self::SHARED_TABLE_CATALOG[$name]) && empty($where)) {
            throw new \Exception("cleanTable() 禁止无条件清空插件间共享表：{$name}，必须传入归属条件只清理本插件数据");
        }
        // 兼容旧版本、升级中断或人工删表后的部分安装状态。
        // rollbackMigrate() 本身通常使用 DROP TABLE IF EXISTS，数据清理也应保持幂等。
        if (!$this->hasTable($name)) {
            return 0;
        }
        $query = Db::table($this->table($name));
        if (!empty($where)) {
            $query->where($where);
            return (int) $query->delete();
        }
        // 无条件清空全表时使用强制删除，避免 ThinkPHP "delete without condition" 拦截
        return (int) $query->delete(true);
    }

    // ============== DIY 页面链接辅助（链接选择器数据） ==============

    /**
     * 确保页面链接分类存在（幂等），返回末级分类 id
     *
     * 页面链接分类树（eb_page_categroy）是共享核心数据，插件只拥有自己的末级分类。
     * 按"名称路径"逐级定位，缺失时创建，全程不硬编码分类 id。
     * 例：['商城页面', '营销链接', '秒杀链接'] → 返回"秒杀链接"分类 id
     *
     * @param array $pathNames 从顶层到末级的分类名路径
     * @param int $sort 末级分类排序（仅创建时生效）
     * @param string $type 末级分类类型（仅创建时生效）：link 静态链接列表；
     *                     seckill/bargain/combination/integral 等动态商品选择分类，
     *                     前端链接选择器按该 type 渲染对应的商品列表
     * @return int 末级分类 id；路径为空时返回 0
     */
    protected function ensurePageLinkCategory(array $pathNames, int $sort = 0, string $type = 'link'): int
    {
        if (empty($pathNames)) {
            return 0;
        }

        $pid = 0;
        $last = count($pathNames) - 1;
        $cateId = 0;
        foreach ($pathNames as $index => $name) {
            $existing = Db::table($this->table('page_categroy'))
                ->where('pid', $pid)
                ->where('name', $name)
                ->find();
            if ($existing) {
                $cateId = (int) $existing['id'];
                $pid = $cateId;
                continue;
            }

            // 中间层级为共享分组，统一按静态链接分类创建；末级分类使用调用方指定的 type
            // （动态数据分类如"秒杀商品"由各活动模块随安装创建，而非核心预置）
            $cateId = (int) Db::table($this->table('page_categroy'))->insertGetId([
                'pid'      => $pid,
                'type'     => $index === $last ? $type : 'link',
                'name'     => $name,
                'sort'     => $index === $last ? $sort : 0,
                'status'   => 1,
                'add_time' => time(),
            ]);
            $pid = $cateId;
        }

        return $cateId;
    }

    /**
     * 批量写入页面链接（幂等，按 cate_id + url 查重）
     *
     * @param int $cateId 分类 id
     * @param array $links 链接配置，形如
     *   [['name' => '秒杀详情', 'url' => '/pages/...', 'param' => 'id,time_id', 'example' => '', 'sort' => 0], ...]
     * @return int 实际新增条数
     */
    protected function seedPageLinks(int $cateId, array $links): int
    {
        if ($cateId <= 0 || empty($links)) {
            return 0;
        }

        $count = 0;
        $now = time();
        foreach ($links as $link) {
            $url = (string) ($link['url'] ?? '');
            if ($url === '') {
                continue;
            }

            // 幂等：url 全局查重——老站点预置在共享分类（如个人中心）的插件链接不重复插入
            $exists = Db::table($this->table('page_link'))
                ->where('url', $url)
                ->find();
            if ($exists) {
                continue;
            }

            Db::table($this->table('page_link'))->insert([
                'cate_id'  => $cateId,
                'type'     => 1,
                'name'     => (string) ($link['name'] ?? ''),
                'url'      => $url,
                'param'    => (string) ($link['param'] ?? ''),
                'example'  => (string) ($link['example'] ?? ''),
                'status'   => 1,
                'sort'     => (int) ($link['sort'] ?? 0),
                'add_time' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * 清理插件页面链接及独有分类（卸载时调用）
     *
     * 清理边界：
     * - 链接：按 url 白名单精确删除（覆盖挂载在独有分类与共享分类两种位置的插件链接）
     * - 分类：仅删除本插件独有命名空间（按分类名精确匹配），且分类下已无链接时才删除，
     *   避免误删商家在插件分类下自建的链接
     *
     * @param array $categoryNames 本插件独有分类名列表（如 ['秒杀链接']）
     * @param array $urls 本插件 seed 的链接 url 白名单
     * @return int 清理条数（链接 + 分类）
     */
    protected function cleanPageLinks(array $categoryNames, array $urls): int
    {
        if ((!$this->hasTable('page_link') || !$this->hasTable('page_categroy'))
            || (empty($categoryNames) && empty($urls))) {
            return 0;
        }

        $count = 0;

        // 1. 按 url 白名单删除插件链接：url 为插件页面路径，语义上仅由本插件 seed 写入
        if (!empty($urls)) {
            $count += Db::table($this->table('page_link'))
                ->whereIn('url', $urls)
                ->delete();
        }

        // 2. 删除已空的独有分类（仍有链接说明商家自建过数据，保留分类避免孤儿数据）
        foreach ($categoryNames as $name) {
            $category = Db::table($this->table('page_categroy'))
                ->where('name', $name)
                ->find();
            if (!$category) {
                continue;
            }
            $remaining = Db::table($this->table('page_link'))
                ->where('cate_id', $category['id'])
                ->count();
            if ($remaining > 0) {
                continue;
            }
            $count += Db::table($this->table('page_categroy'))
                ->where('id', $category['id'])
                ->delete();
        }

        return $count;
    }

    /** 获取当前 Installer 对应的复合插件标识。 */
    protected function getOwnerPluginId(): string
    {
        if ($this->ownerPluginId !== null) {
            return $this->ownerPluginId;
        }
        if (str_starts_with($this->getKey(), 'module:') || str_starts_with($this->getKey(), 'driver:')) {
            return $this->ownerPluginId = $this->getKey();
        }

        try {
            $reflection = new \ReflectionClass($this);
            $manifestFile = dirname((string) $reflection->getFileName()) . '/plugin.json';
            if (is_file($manifestFile)) {
                $manifest = \crmeb\plugin\PluginManifest::fromFile($manifestFile, root_path());
                return $this->ownerPluginId = $manifest->identity->canonicalId();
            }
        } catch (\Throwable $e) {
        }
        return $this->ownerPluginId = $this->getKey();
    }

    /**
     * 驱动安装时把 plugin.json 元数据写入统一驱动字典。
     * 目录只负责安装前发现，运行时不再扫描目录。
     */
    protected function installDriverRecord(): bool
    {
        if ($this->getType() !== 'driver') {
            return false;
        }

        $manifest = $this->getPluginManifest();
        $driver = $manifest->meta['driver'] ?? [];
        if (!is_array($driver) || empty($driver['driver_key'])) {
            throw new \RuntimeException("驱动插件缺少 driver 元数据: {$this->getKey()}");
        }

        $json = static fn ($value): string => json_encode(
            is_array($value) ? $value : [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '[]';
        $namespace = 'driver\\' . $manifest->identity->category . '\\' . basename($manifest->basePath);
        $now = time();
        $row = [
            'driver_type' => $manifest->identity->category,
            'driver_key' => (string) $driver['driver_key'],
            'driver_name' => (string) ($driver['name'] ?? $manifest->name),
            'class_name' => (string) ($driver['class_name'] ?? 'Driver'),
            'namespace' => $namespace,
            'owner_plugin_id' => $manifest->identity->canonicalId(),
            'icon' => (string) ($driver['icon'] ?? ''),
            'description' => (string) ($manifest->meta['description'] ?? ''),
            'is_builtin' => !empty($driver['is_builtin']) ? 1 : 0,
            'capabilities' => $json($driver['capabilities'] ?? []),
            'config_fields' => $json($driver['config_fields'] ?? []),
            'help_doc' => $json($driver['help_doc'] ?? []),
            'domain_template' => (string) ($driver['domain_template'] ?? ''),
            'bucket_fields' => $json($driver['bucket_fields'] ?? []),
            'thumb_watermark_config' => $json($driver['thumb_watermark_config'] ?? []),
            'platforms' => $json($driver['platforms'] ?? []),
            'sort' => (int) ($driver['sort'] ?? 0),
            'status' => !empty($driver['is_builtin']) ? 1 : 0,
            'add_time' => $now,
            'update_time' => $now,
        ];

        Db::table($this->table('driver'))->duplicate([
            'driver_name', 'class_name', 'namespace', 'owner_plugin_id', 'icon', 'description',
            'is_builtin', 'capabilities', 'config_fields', 'help_doc', 'domain_template',
            'bucket_fields', 'thumb_watermark_config', 'platforms', 'sort', 'update_time',
        ])->insert($row);
        return true;
    }

    /** 驱动卸载时删除当前插件拥有的字典记录，并兼容升级前的无归属旧记录。 */
    protected function removeDriverRecord(): bool
    {
        if ($this->getType() !== 'driver') {
            return false;
        }

        $count = (int) Db::table($this->table('driver'))
            ->where('owner_plugin_id', $this->getOwnerPluginId())
            ->delete();

        $manifest = $this->getPluginManifest();
        $driver = $manifest->meta['driver'] ?? [];
        if (is_array($driver) && !empty($driver['driver_key'])) {
            $count += (int) Db::table($this->table('driver'))
                ->where('owner_plugin_id', '')
                ->where('driver_type', $manifest->identity->category)
                ->where('driver_key', (string) $driver['driver_key'])
                ->delete();
        }

        return $count > 0;
    }

    protected function getPluginManifest(): PluginManifest
    {
        $reflection = new \ReflectionClass($this);
        $manifestFile = dirname((string) $reflection->getFileName()) . '/plugin.json';
        if (!is_file($manifestFile)) {
            throw new \RuntimeException("插件清单不存在: {$manifestFile}");
        }
        return PluginManifest::fromFile($manifestFile, root_path());
    }

    /** @return array<string, int> */
    protected function captureSharedResourceWatermarks(): array
    {
        $watermarks = [];
        foreach (['system_menus', 'system_config', 'system_config_tab'] as $table) {
            if ($this->supportsOwnershipColumn($table)) {
                $watermarks[$table] = (int) Db::table($this->table($table))->max('id');
            }
        }
        return $watermarks;
    }

    /** seed() 中新增的公共配置资源统一补上插件所有权。 */
    protected function claimSharedResources(array $watermarks): void
    {
        $owner = $this->getOwnerPluginId();
        foreach ($watermarks as $table => $maxId) {
            Db::table($this->table($table))
                ->where('id', '>', $maxId)
                ->where('owner_plugin_id', '')
                ->update(['owner_plugin_id' => $owner]);
        }
    }

    protected function removeOwnedSharedResources(): int
    {
        $owner = $this->getOwnerPluginId();
        $count = 0;
        foreach (['system_config', 'system_config_tab', 'system_menus'] as $table) {
            if ($this->supportsOwnershipColumn($table)) {
                $count += (int) Db::table($this->table($table))
                    ->where('owner_plugin_id', $owner)
                    ->delete();
            }
        }
        return $count;
    }

    protected function supportsOwnershipColumn(string $table): bool
    {
        if (isset($this->ownershipColumnCache[$table])) {
            return $this->ownershipColumnCache[$table];
        }
        try {
            $database = (string) config('database.connections.mysql.database');
            $exists = Db::query(
                'SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
                [$database, $this->table($table), 'owner_plugin_id']
            );
            return $this->ownershipColumnCache[$table] = !empty($exists);
        } catch (\Throwable $e) {
            return $this->ownershipColumnCache[$table] = false;
        }
    }

    /**
     * 判断菜单稳定标识字段是否存在。
     *
     * @return bool
     */
    protected function supportsMenuKeyColumn(): bool
    {
        if ($this->menuKeyColumnSupported !== null) {
            return $this->menuKeyColumnSupported;
        }
        try {
            $database = (string)config('database.connections.mysql.database');
            $exists = Db::query(
                'SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
                [$database, $this->table('system_menus'), 'menu_key']
            );
            return $this->menuKeyColumnSupported = !empty($exists);
        } catch (\Throwable $e) {
            return $this->menuKeyColumnSupported = false;
        }
    }

    /**
     * 为存量数据库补齐插件公共资源归属字段。
     *
     * 新安装库已由 crmeb_table.sql 创建这些字段；这里用于兼容从旧版本直接升级、
     * 未完整执行历史 SQL 的站点。DDL 必须在安装/卸载事务开始前执行。
     */
    protected function ensureSharedOwnershipColumns(): void
    {
        $definitions = [
            'system_menus' => "varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为核心菜单' AFTER `mark`",
            'system_config' => "varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为核心配置' AFTER `link_value`",
            'system_config_tab' => "varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为核心分组' AFTER `menus_id`",
            'agreement' => "varchar(96) NOT NULL DEFAULT '' COMMENT '所属插件复合标识，空为系统协议' AFTER `type`",
        ];

        foreach ($definitions as $table => $definition) {
            $fullTable = $this->table($table);
            if (!$this->supportsOwnershipColumn($table)) {
                Db::execute("ALTER TABLE `{$fullTable}` ADD COLUMN `owner_plugin_id` {$definition}");
                $this->ownershipColumnCache[$table] = true;
            }

            $database = (string) config('database.connections.mysql.database');
            $indexes = Db::query(
                'SELECT 1 FROM information_schema.statistics'
                . ' WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$database, $fullTable, 'idx_owner_plugin_id']
            );
            if (empty($indexes)) {
                Db::execute("ALTER TABLE `{$fullTable}` ADD INDEX `idx_owner_plugin_id` (`owner_plugin_id`)");
            }
        }

        // 协议表插件化改造：补齐 AI 提示词字段并改造唯一索引，供插件 seed 协议使用
        $this->ensureAgreementColumns();

        // 菜单稳定标识：旧库回填并建立唯一约束，供全部插件按 key 幂等管理。
        $this->ensureMenuKeyInfrastructure();

        // 共享表准入登记表兜底建表（存量站点升级，DDL 幂等）
        $this->ensureSharedTableRegistry();
    }

    /**
     * 为存量数据库补齐菜单稳定标识及唯一索引。
     *
     * 核心菜单在 unique_auth 全局唯一时使用可读的 core:<unique_auth>；
     * 其余核心菜单与插件菜单使用稳定字段哈希。历史重复行保留最早记录的
     * 标准 key，其余行改为 legacy key，确保添加唯一索引不会中断升级。
     *
     * @return void
     */
    protected function ensureMenuKeyInfrastructure(): void
    {
        $fullTable = $this->table('system_menus');
        if (!$this->supportsMenuKeyColumn()) {
            Db::execute(
                "ALTER TABLE `{$fullTable}` ADD COLUMN `menu_key` varchar(190) "
                . "NOT NULL DEFAULT '' COMMENT '菜单稳定标识，系统生成且不可修改' AFTER `unique_auth`"
            );
            $this->menuKeyColumnSupported = true;
        }

        $identitySql = "CONCAT_WS(CHAR(31), CAST(m.`auth_type` AS CHAR), m.`unique_auth`, m.`api_url`, "
            . "UPPER(m.`methods`), m.`controller`, m.`action`, m.`menu_name`)";
        Db::execute(
            "UPDATE `{$fullTable}` AS m "
            . "LEFT JOIN (SELECT `unique_auth`, COUNT(*) AS total FROM `{$fullTable}` "
            . "WHERE `unique_auth` <> '' GROUP BY `unique_auth`) AS u ON u.`unique_auth` = m.`unique_auth` "
            . "SET m.`menu_key` = CASE "
            . "WHEN m.`owner_plugin_id` = '' AND m.`unique_auth` <> '' AND u.`total` = 1 "
            . "THEN CONCAT('core:', m.`unique_auth`) "
            . "WHEN m.`owner_plugin_id` = '' THEN CONCAT('core:menu:', SHA1({$identitySql})) "
            . "ELSE CONCAT(m.`owner_plugin_id`, ':menu:', SHA1({$identitySql})) END "
            . "WHERE m.`menu_key` = ''"
        );

        // 历史重复菜单先分配仅用于清理的 legacy key，保证唯一索引可安全建立。
        Db::execute(
            "UPDATE `{$fullTable}` AS m INNER JOIN ("
            . "SELECT `menu_key`, MIN(`id`) AS keep_id FROM `{$fullTable}` "
            . "WHERE `menu_key` <> '' GROUP BY `menu_key` HAVING COUNT(*) > 1"
            . ") AS d ON d.`menu_key` = m.`menu_key` AND m.`id` <> d.`keep_id` "
            . "SET m.`menu_key` = CONCAT('legacy:menu:', m.`id`, ':', SHA1(m.`menu_key`))"
        );

        $database = (string)config('database.connections.mysql.database');
        // MySQL 5.7 不支持函数索引，改用 STORED 生成列 + 普通唯一索引。
        // NULLIF 生成 NULL，允许空 key 重复；非空 key 仍由唯一索引约束。
        $generatedColumn = Db::query(
            'SELECT 1 FROM information_schema.columns'
            . ' WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
            [$database, $fullTable, 'menu_key_unique']
        );
        if (empty($generatedColumn)) {
            Db::execute(
                "ALTER TABLE `{$fullTable}` ADD COLUMN `menu_key_unique` varchar(190) "
                . "GENERATED ALWAYS AS (NULLIF(`menu_key`, '')) STORED "
                . "COMMENT '菜单稳定标识唯一键（空值不参与唯一约束）' AFTER `menu_key`"
            );
        }
        $index = Db::query(
            'SELECT 1 FROM information_schema.statistics'
            . ' WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $fullTable, 'uk_menu_key']
        );
        if (empty($index)) {
            Db::execute("ALTER TABLE `{$fullTable}` ADD UNIQUE KEY `uk_menu_key` (`menu_key_unique`)");
        }
    }

    /**
     * 补齐协议表插件化改造所需字段与索引（幂等，DDL 在安装/卸载事务外执行）
     *
     * 存量站点升级内容：
     * - 补 system_prompt / user_prompt 字段（AI 写作提示词，随协议数据管理）
     * - 原 type 单列唯一索引改造为 (type, owner_plugin_id) 复合唯一索引，
     *   允许不同插件复用相同协议类型值
     *
     * @return void
     */
    protected function ensureAgreementColumns(): void
    {
        if (!$this->hasTable('agreement')) {
            return;
        }
        $database   = (string) config('database.connections.mysql.database');
        $fullTable  = $this->table('agreement');

        // 补齐 AI 提示词字段
        $columns = Db::query(
            'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
            [$database, $fullTable]
        );
        // 不同 MySQL 驱动/版本返回的 information_schema 字段名大小写可能不同，做兼容读取。
        $names = array_map(
            static fn ($row): string => (string)($row['COLUMN_NAME'] ?? $row['column_name'] ?? ''),
            $columns
        );
        if (!in_array('system_prompt', $names, true)) {
            Db::execute("ALTER TABLE `{$fullTable}` ADD COLUMN `system_prompt` text COMMENT '系统AI提示词（AI写作的角色设定与格式要求）' AFTER `content`");
        }
        if (!in_array('user_prompt', $names, true)) {
            Db::execute("ALTER TABLE `{$fullTable}` ADD COLUMN `user_prompt` text COMMENT '用户提示词（AI写作输入框默认提示词）' AFTER `system_prompt`");
        }

        // 原 type 单列唯一索引改造为 (type, owner_plugin_id) 复合唯一索引
        $compositeExists = Db::query(
            'SELECT 1 FROM information_schema.statistics'
            . ' WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $fullTable, 'uk_type_plugin']
        );
        if (empty($compositeExists)) {
            // 删除旧的单列唯一索引（历史索引名为 type，仅当其确实建在 type 列上时删除）
            $legacy = Db::query(
                "SELECT 1 FROM information_schema.statistics"
                . " WHERE table_schema = ? AND table_name = ? AND index_name = 'type' AND column_name = 'type' LIMIT 1",
                [$database, $fullTable]
            );
            if (!empty($legacy)) {
                Db::execute("ALTER TABLE `{$fullTable}` DROP INDEX `type`");
            }
            Db::execute("ALTER TABLE `{$fullTable}` ADD UNIQUE KEY `uk_type_plugin` (`type`,`owner_plugin_id`)");
        }
    }

    // ============== 插件协议种子辅助（需要协议的插件在 seed() 中调用） ==============

    /**
     * 批量写入插件协议种子（幂等）
     *
     * 幂等与存量数据迁移规则：
     * - 已存在本插件同 type 协议：跳过（保留商家修改过的内容）
     * - 存在同 type 的无归属记录（旧版核心预置数据）：认领——补插件归属与 AI 提示词，
     *   不覆盖商家已修改的协议内容
     * - 均不存在：插入带默认内容的新记录
     *
     * @param array $agreements 每项形如：
     *   ['type' => 2, 'title' => '代理商协议', 'content' => '<h1>...</h1>',
     *    'system_prompt' => '...', 'user_prompt' => '...', 'status' => 1, 'sort' => 0]
     * @return int 实际写入/认领条数
     */
    protected function seedAgreements(array $agreements): int
    {
        if (!$this->hasTable('agreement') || empty($agreements)) {
            return 0;
        }

        $owner = $this->getOwnerPluginId();
        $now   = time();
        $count = 0;

        foreach ($agreements as $item) {
            $type = (int) ($item['type'] ?? 0);
            if ($type <= 0) {
                continue;
            }

            $data = [
                'title'         => (string) ($item['title'] ?? ''),
                'content'       => (string) ($item['content'] ?? ''),
                'system_prompt' => (string) ($item['system_prompt'] ?? ''),
                'user_prompt'   => (string) ($item['user_prompt'] ?? ''),
                'status'        => (int) ($item['status'] ?? 1),
            ];

            // 本插件已有同类型协议：跳过，避免覆盖商家已修改的内容
            $owned = Db::table($this->table('agreement'))
                ->where('type', $type)
                ->where('owner_plugin_id', $owner)
                ->find();
            if ($owned) {
                continue;
            }

            // 旧版核心预置的同类型无归属记录：认领归属并补齐 AI 提示词，协议内容保持不动
            $legacy = Db::table($this->table('agreement'))
                ->where('type', $type)
                ->where('owner_plugin_id', '')
                ->find();
            if ($legacy) {
                Db::table($this->table('agreement'))
                    ->where('id', $legacy['id'])
                    ->update([
                        'owner_plugin_id' => $owner,
                        'system_prompt'   => $data['system_prompt'],
                        'user_prompt'     => $data['user_prompt'],
                    ]);
                $count++;
                continue;
            }

            // 全新插入
            Db::table($this->table('agreement'))->insert(array_merge($data, [
                'type'            => $type,
                'owner_plugin_id' => $owner,
                'sort'            => (int) ($item['sort'] ?? 0),
                'add_time'        => $now,
            ]));
            $count++;
        }

        return $count;
    }

    /**
     * 卸载时清理插件协议数据（cleanData 中调用）
     *
     * 清理边界：
     * - 本插件拥有的协议（owner_plugin_id 匹配，必删）
     * - 无归属但 type 在本插件白名单内的旧数据（存量站点未重装插件即卸载的兜底）
     *
     * @param array $types 本插件使用的协议类型值白名单（如 [2, 8]）
     * @return int 清理条数
     */
    protected function cleanAgreements(array $types = []): int
    {
        if (!$this->hasTable('agreement')) {
            return 0;
        }

        $count = (int) Db::table($this->table('agreement'))
            ->where('owner_plugin_id', $this->getOwnerPluginId())
            ->delete();

        if (!empty($types)) {
            $count += (int) Db::table($this->table('agreement'))
                ->where('owner_plugin_id', '')
                ->whereIn('type', $types)
                ->delete();
        }

        return $count;
    }

    /**
     * 软删除菜单（用于系统原生菜单，卸载时不物理删除）
     * 按 menu_name + controller 匹配，将 is_del 置为 1
     * @return int
     */
    protected function softDeleteMenus(): int
    {
        $menus = $this->getMenuConfig();
        $count = 0;
        foreach ($menus as $menu) {
            $count += Db::table($this->prefix . 'system_menus')
                ->where('menu_name', $menu['menu_name'])
                ->where('controller', $menu['controller'] ?? '')
                ->update(['is_del' => 1]);
        }
        return $count;
    }

    /**
     * 备份插件文件到 backup/ 目录
     *
     * 卸载时自动将 modules/ 下的插件目录移动到 backend/backup/ 下，
     * 备份目录名格式：{路径}_{时间戳}（如 agent_20260720_153000、product_normal_20260720_153000）。
     *
     * 使用 register_shutdown_function 延迟执行，避免文件移动时 PHP 进程锁冲突。
     *
     * @return void
     */
    protected function backupPluginFiles(): void
    {
        $ref = new \ReflectionClass($this);
        $moduleDir = dirname($ref->getFileName());

        $rootPath = app()->getRootPath();
        $backupDir = $rootPath . 'backup' . DIRECTORY_SEPARATOR;

        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        // 计算相对于 backend/ 的路径，将 / 替换为 _，追加时间戳
        $relativePath = ltrim(substr($moduleDir, strlen(rtrim($rootPath, DIRECTORY_SEPARATOR))), DIRECTORY_SEPARATOR);
        $backupName = str_replace(DIRECTORY_SEPARATOR, '_', $relativePath) . '_' . date('Ymd_His');
        $backupPath = $backupDir . $backupName;

        // 延迟执行，避免文件移动影响当前请求
        register_shutdown_function(function () use ($moduleDir, $backupPath) {
            if (is_dir($moduleDir) && !is_dir($backupPath)) {
                rename($moduleDir, $backupPath);
            }
        });
    }
}
