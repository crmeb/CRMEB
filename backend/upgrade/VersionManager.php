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

namespace upgrade;

use think\facade\Config;
use think\facade\Db;
use think\facade\Log;
use crmeb\exceptions\AdminException;

/**
 * 版本管理器
 * 用于跨版本升级管理
 * Class VersionManager
 * @package upgrade
 */
class VersionManager
{
    /**
     * 配置
     * @var array
     */
    protected $config = [];

    /**
     * 数据库表前缀
     * @var string
     */
    protected $prefix = '';

    /**
     * 当前版本信息
     * @var array
     */
    protected $currentVersion = [];

    /**
     * SQL类型说明
     */
    const SQL_TYPE_CREATE_TABLE = 1;    // 建表
    const SQL_TYPE_DROP_TABLE = 2;       // 删表
    const SQL_TYPE_ADD_COLUMN = 3;       // 添加字段
    const SQL_TYPE_MODIFY_COLUMN = 4;    // 修改字段
    const SQL_TYPE_DROP_COLUMN = 5;      // 删除字段
    const SQL_TYPE_INSERT_DATA = 6;      // 添加数据
    const SQL_TYPE_UPDATE_DATA = 7;      // 修改数据
    const SQL_TYPE_DELETE_DATA = 8;      // 删除数据
    const SQL_TYPE_RAW = -1;             // 直接执行SQL

    /**
     * VersionManager constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('upgrade', []);
        $this->prefix = config('database.connections.' . config('database.default'))['prefix'];
        $this->currentVersion = $this->getCurrentVersion();
    }

    /**
     * 获取当前系统版本信息
     * @return array
     */
    public function getCurrentVersion(): array
    {
        $file = app()->getRootPath() . '.version';
        $arr = [];

        if (!file_exists($file)) {
            return $arr;
        }

        $list = @file($file);
        if (!$list) {
            return $arr;
        }

        foreach ($list as $val) {
            $val = str_replace(["\r", "\n", "\t"], '', $val);
            if (strpos($val, '=') !== false) {
                list($k, $v) = explode('=', $val, 2);
                $arr[trim($k)] = trim($v);
            }
        }

        return $arr;
    }

    /**
     * 获取当前版本代码
     * @return int
     */
    public function getCurrentVersionCode(): int
    {
        return (int)($this->currentVersion['version_code'] ?? 0);
    }

    /**
     * 获取当前版本名称
     * @return string
     */
    public function getCurrentVersionName(): string
    {
        return $this->currentVersion['version'] ?? '';
    }

    /**
     * 获取所有版本列表
     * @return array
     */
    public function getAllVersions(): array
    {
        return $this->config['versions'] ?? [];
    }

    /**
     * 获取最新版本信息
     * @return array
     */
    public function getLatestVersion(): array
    {
        $versions = $this->getAllVersions();
        return end($versions) ?: [];
    }

    /**
     * 获取需要升级的版本列表
     * 从当前版本到最新版本之间的所有版本
     * @return array
     */
    public function getPendingVersions(): array
    {
        $currentCode = $this->getCurrentVersionCode();
        $versions = $this->getAllVersions();
        $pending = [];
        foreach ($versions as $version) {
            if ($version['code'] > $currentCode) {
                $pending[] = $version;
            }
        }

        // 按版本代码从小到大排序
        usort($pending, function ($a, $b) {
            return $a['code'] - $b['code'];
        });

        return $pending;
    }

    /**
     * 获取版本升级差距
     * @return int
     */
    public function getVersionGap(): int
    {
        return count($this->getPendingVersions());
    }

    /**
     * 是否需要升级
     * @return bool
     */
    public function needUpgrade(): bool
    {
        return $this->getVersionGap() > 0;
    }

    /**
     * 获取最低版本要求配置
     * @return array
     */
    public function getMinVersionConfig(): array
    {
        return $this->config['min_version'] ?? [];
    }

    /**
     * 检查当前版本是否满足最低版本要求
     * @return bool
     */
    public function meetsMinVersionRequirement(): bool
    {
        $minVersion = $this->getMinVersionConfig();
        if (empty($minVersion)) {
            return true; // 未配置最低版本，默认允许
        }

        $minCode = $minVersion['code'] ?? 0;
        $currentCode = $this->getCurrentVersionCode();

        return $currentCode >= $minCode;
    }

    /**
     * 获取最低版本错误提示信息
     * @return string
     */
    public function getMinVersionMessage(): string
    {
        $minVersion = $this->getMinVersionConfig();
        return $minVersion['message'] ?? '当前版本不支持跨版本在线升级功能';
    }

    /**
     * 检查跨版本升级可用性
     * @return array ['available' => bool, 'message' => string, 'current_version' => string, 'min_version' => string]
     */
    public function checkUpgradeAvailability(): array
    {
        $currentCode = $this->getCurrentVersionCode();
        $currentVersion = $this->getCurrentVersionName();
        $minVersion = $this->getMinVersionConfig();

        if (empty($minVersion)) {
            return [
                'available' => true,
                'message' => '可以使用跨版本升级',
                'current_version' => $currentVersion,
                'current_code' => $currentCode,
                'min_version' => '',
                'min_code' => 0
            ];
        }

        $minCode = $minVersion['code'] ?? 0;
        $available = $currentCode >= $minCode;

        return [
            'available' => $available,
            'message' => $available ? '可以使用跨版本升级' : $this->getMinVersionMessage(),
            'current_version' => $currentVersion,
            'current_code' => $currentCode,
            'min_version' => $minVersion['version'] ?? '',
            'min_code' => $minCode
        ];
    }

    /**
     * 获取版本升级脚本
     * @param array $version
     * @return array
     */
    public function getVersionUpgradeData(array $version): array
    {
        $filePath = ($this->config['upgrade_path'] ?? '') . ($version['file'] ?? '');

        if (!file_exists($filePath)) {
            return [];
        }

        $data = include $filePath;
        return is_array($data) ? $data : [];
    }

    /**
     * 获取所有待执行的升级SQL
     * @return array
     */
    public function getAllPendingUpgradeSql(): array
    {
        $pendingVersions = $this->getPendingVersions();
        $allSql = [];

        foreach ($pendingVersions as $version) {
            $upgradeData = $this->getVersionUpgradeData($version);
            if (!empty($upgradeData['update_sql'])) {
                foreach ($upgradeData['update_sql'] as $sql) {
                    $sql['version'] = $version['version'];
                    $sql['version_code'] = $version['code'];
                    $allSql[] = $sql;
                }
            }
        }

        return $allSql;
    }

    /**
     * 执行单条升级SQL
     * @param array $sqlItem
     * @return array ['success' => bool, 'message' => string]
     */
    public function executeSqlItem(array $sqlItem): array
    {
        $type = $sqlItem['type'] ?? 0;
        $table = $this->prefix . ($sqlItem['table'] ?? '');
        $field = $sqlItem['field'] ?? '';
        $findSql = $sqlItem['findSql'] ?? '';
        $sql = $sqlItem['sql'] ?? '';
        $whereSql = $sqlItem['whereSql'] ?? '';
        $whereTable = isset($sqlItem['whereTable']) ? $this->prefix . $sqlItem['whereTable'] : '';
        $newTable = isset($sqlItem['new_table']) ? $this->prefix . $sqlItem['new_table'] : '';

        try {
            // 替换表名
            if ($findSql) {
                $findSql = str_replace('@table', $table, $findSql);
            }

            // 预检查
            if ($findSql) {
                $exists = !empty(Db::query($findSql));

                switch ($type) {
                    case self::SQL_TYPE_CREATE_TABLE:
                    case self::SQL_TYPE_ADD_COLUMN:
                    case self::SQL_TYPE_INSERT_DATA:
                        if ($exists) {
                            return ['success' => true, 'message' => $this->getSkipMessage($type, $table, $field), 'skipped' => true];
                        }
                        break;
                    case self::SQL_TYPE_MODIFY_COLUMN:
                    case self::SQL_TYPE_DROP_COLUMN:
                    case self::SQL_TYPE_UPDATE_DATA:
                        if (!$exists) {
                            return ['success' => true, 'message' => $this->getSkipMessage($type, $table, $field), 'skipped' => true];
                        }
                        break;
                    case self::SQL_TYPE_DELETE_DATA:
                        if (!$exists) {
                            return ['success' => true, 'message' => '数据不存在，跳过删除', 'skipped' => true];
                        }
                        break;
                }
            }

            // 替换SQL中的占位符
            $execSql = str_replace('@table', $table, $sql);

            // 处理关联表查询
            if (in_array($type, [self::SQL_TYPE_INSERT_DATA, self::SQL_TYPE_UPDATE_DATA]) && $whereSql && $whereTable) {
                $whereSql = str_replace('@whereTable', $whereTable, $whereSql);
                $result = Db::query($whereSql);
                $tabId = $result[0]['tabId'] ?? 0;
                if (!$tabId) {
                    return ['success' => true, 'message' => '关联数据不存在，跳过', 'skipped' => true];
                }
                $execSql = str_replace('@tabId', $tabId, $execSql);
            }

            // 处理新表名
            if ($type == self::SQL_TYPE_RAW && $newTable) {
                $execSql = str_replace('@new_table', $newTable, $execSql);
            }

            // 执行SQL
            if ($execSql) {
                Db::execute($execSql);
                Log::write(['type' => 'upgrade_sql', 'sql' => $execSql, 'item' => json_encode($sqlItem)], 'notice');
            }

            return ['success' => true, 'message' => $this->getSuccessMessage($type, $table, $field)];

        } catch (\Throwable $e) {
            Log::error(['type' => 'upgrade_sql_error', 'error' => $e->getMessage(), 'item' => json_encode($sqlItem)]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取跳过消息
     */
    protected function getSkipMessage(int $type, string $table, string $field): string
    {
        $messages = [
            self::SQL_TYPE_CREATE_TABLE => "{$table} 表已存在",
            self::SQL_TYPE_DROP_TABLE => "{$table} 表不存在",
            self::SQL_TYPE_ADD_COLUMN => "{$table} 表中 {$field} 字段已存在",
            self::SQL_TYPE_MODIFY_COLUMN => "{$table} 表中 {$field} 字段不存在",
            self::SQL_TYPE_DROP_COLUMN => "{$table} 表中 {$field} 字段不存在",
            self::SQL_TYPE_INSERT_DATA => "{$table} 数据已存在",
            self::SQL_TYPE_UPDATE_DATA => "{$table} 数据不存在",
        ];
        return $messages[$type] ?? '跳过';
    }

    /**
     * 获取成功消息
     */
    protected function getSuccessMessage(int $type, string $table, string $field): string
    {
        $messages = [
            self::SQL_TYPE_CREATE_TABLE => "{$table} 表创建成功",
            self::SQL_TYPE_DROP_TABLE => "{$table} 表删除成功",
            self::SQL_TYPE_ADD_COLUMN => "{$table} 表中 {$field} 字段添加成功",
            self::SQL_TYPE_MODIFY_COLUMN => "{$table} 表中 {$field} 字段修改成功",
            self::SQL_TYPE_DROP_COLUMN => "{$table} 表中 {$field} 字段删除成功",
            self::SQL_TYPE_INSERT_DATA => "{$table} 数据添加成功",
            self::SQL_TYPE_UPDATE_DATA => "{$table} 数据修改成功",
            self::SQL_TYPE_DELETE_DATA => "{$table} 数据删除成功",
            self::SQL_TYPE_RAW => "{$table} SQL执行成功",
        ];
        return $messages[$type] ?? '执行成功';
    }

    /**
     * 更新版本文件
     * @param string $version
     * @param int $code
     * @return bool
     */
    public function updateVersionFile(string $version, int $code): bool
    {
        $file = app()->getRootPath() . '.version';
        $data = $this->getCurrentVersion();

        $data['version'] = $version;
        $data['version_code'] = $code;
        $data['platform'] = $this->config['platform'] ?? 'CRMEB';
        $data['app_id'] = $data['app_id'] ?? ($this->config['app_id'] ?? '');
        $data['app_key'] = $data['app_key'] ?? ($this->config['app_key'] ?? '');

        $content = '';
        foreach ($data as $key => $value) {
            $content .= "{$key}={$value}\n";
        }

        return file_put_contents($file, $content) !== false;
    }

    /**
     * 获取升级概览信息
     * @return array
     */
    public function getUpgradeOverview(): array
    {
        $currentVersion = $this->getCurrentVersionName();
        $currentCode = $this->getCurrentVersionCode();
        $latestVersion = $this->getLatestVersion();
        $pendingVersions = $this->getPendingVersions();

        return [
            'current_version' => $currentVersion,
            'current_code' => $currentCode,
            'latest_version' => $latestVersion['version'] ?? '',
            'latest_code' => $latestVersion['code'] ?? 0,
            'need_upgrade' => $this->needUpgrade(),
            'version_gap' => $this->getVersionGap(),
            'pending_versions' => array_map(function ($v) {
                return [
                    'version' => $v['version'],
                    'code' => $v['code'],
                    'description' => $v['description'] ?? ''
                ];
            }, $pendingVersions)
        ];
    }

    /**
     * 获取所有待执行的数据迁移处理器
     * @return array
     */
    public function getAllPendingDataHandlers(): array
    {
        $pendingVersions = $this->getPendingVersions();
        $allHandlers = [];

        foreach ($pendingVersions as $version) {
            $upgradeData = $this->getVersionUpgradeData($version);
            if (!empty($upgradeData['data_handlers'])) {
                foreach ($upgradeData['data_handlers'] as $handler) {
                    $handler['version'] = $version['version'];
                    $handler['version_code'] = $version['code'];
                    $allHandlers[] = $handler;
                }
            }
        }

        return $allHandlers;
    }

    /**
     * 获取指定版本的数据迁移处理器
     * @param array $version
     * @return array
     */
    public function getVersionDataHandlers(array $version): array
    {
        $upgradeData = $this->getVersionUpgradeData($version);
        return $upgradeData['data_handlers'] ?? [];
    }
}
