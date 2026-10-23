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

namespace app\services\system\module;

use think\facade\Db;

/**
 * 模块安装日志表迁移
 * 创建 eb_module_install_log 表
 * 本类为模块安装器生命周期辅助类，Db::query/Db::execute 用于建表/查表等 DDL 操作，
 * 保留直接调用。
 */
class CreateModuleInstallLogTable
{
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

    /**
     * 执行迁移
     * @return void
     */
    public function up(): void
    {
        if ($this->hasTable('module_install_log')) {
            // 向后兼容：已有表补充 enabled 字段
            $this->ensureEnabledColumn();
            // 向后兼容：已有表补充 online_version 字段（应用市场在线最新版本）
            $this->ensureOnlineVersionColumn();
            return;
        }

        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('module_install_log')}` (
            `id`              int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
            `module_key`      varchar(64) NOT NULL DEFAULT '' COMMENT '模块标识 product/hotel, activity/seckill',
            `module_name`     varchar(64) NOT NULL DEFAULT '' COMMENT '模块名称',
            `module_type`     varchar(32) NOT NULL DEFAULT '' COMMENT '模块类型 product_type/activity',
            `version`         varchar(32) NOT NULL DEFAULT '1.0.0' COMMENT '模块版本',
            `online_version`  varchar(32) NOT NULL DEFAULT '' COMMENT '在线最新版本（应用市场检测更新）',
            `status`          tinyint(1) NOT NULL DEFAULT 1 COMMENT '1已安装 0已卸载',
            `enabled`         tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未启用 1已启用',
            `install_time`    int NOT NULL DEFAULT 0 COMMENT '安装时间',
            `uninstall_time`  int NOT NULL DEFAULT 0 COMMENT '卸载时间',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_module_key` (`module_key`),
            KEY `idx_status` (`status`),
            KEY `idx_module_type` (`module_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='模块安装日志表';");
    }

    /**
     * 回滚迁移
     * @return void
     */
    public function down(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('module_install_log')}`;");
    }

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
     * 执行 SQL
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
     * 确保 enabled 字段存在（向后兼容已有表）
     * @return void
     */
    protected function ensureEnabledColumn(): void
    {
        try {
            $database = config('database.connections.mysql.database');
            $result = Db::query(
                "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = 'enabled'",
                [$database, $this->table('module_install_log')]
            );
            if (empty($result)) {
                $this->exec("ALTER TABLE `{$this->table('module_install_log')}` ADD COLUMN `enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未启用 1已启用' AFTER `status`");
            }
        } catch (\Throwable $e) {
            // 忽略，表可能不存在或其他异常
        }
    }

    /**
     * 确保 online_version 字段存在（向后兼容已有表）
     * 用于存储应用市场检测到的在线最新版本，供插件列表对比提示升级
     * @return void
     */
    protected function ensureOnlineVersionColumn(): void
    {
        try {
            $database = config('database.connections.mysql.database');
            $result = Db::query(
                "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = 'online_version'",
                [$database, $this->table('module_install_log')]
            );
            if (empty($result)) {
                $this->exec("ALTER TABLE `{$this->table('module_install_log')}` ADD COLUMN `online_version` varchar(32) NOT NULL DEFAULT '' COMMENT '在线最新版本（应用市场检测更新）' AFTER `version`");
            }
        } catch (\Throwable $e) {
            // 忽略，表可能不存在或其他异常
        }
    }
}
