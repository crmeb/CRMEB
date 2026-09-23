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

namespace modules\service\mcp\tools;

use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;
use think\facade\Db;

/**
 * MCP 工具：数据表结构查询（表清单 / 字段 / 注释 / 索引）
 *
 * 面向 AI 二开者的"运行时知识"入口：直接读取当前运行库的表结构，
 * 相比静态安装 SQL 快照更真实（含模块 Installer 建出的插件表）。
 * 仅返回结构元数据（字段名 / 类型 / 注释 / 索引），不返回任何业务行数据。
 *
 * 表名约定与字段规范见 .crmeb/rules/数据库规范.md。
 */
class DevTableSchemaTool implements McpToolInterface
{
    /**
     * 表名字符合法性（含前缀）；非法即拒绝，杜绝 SQL 拼接风险
     */
    private const TABLE_PATTERN = '/^[A-Za-z0-9_]+$/';

    /**
     * 表清单最多返回条数
     */
    private const MAX_TABLES = 100;

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_dev_table_schema';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询数据表结构：keyword 模糊搜索表名（如 user、order、product）返回表清单；'
            . 'table 传具体表名（如 eb_user 或 user）返回字段名、类型、默认值、字段注释与索引。'
            . '读取的是当前运行库真实结构（含已安装插件的表）。开发 / 二开前先用本工具确认表与字段，'
            . '不要凭记忆猜字段名；只返回结构元数据，不含业务数据';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '表结构查询';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => [
                    'type'        => 'string',
                    'description' => '表名关键词（模糊匹配，如 user 返回 user 相关表清单），与 table 二选一',
                ],
                'table' => [
                    'type'        => 'string',
                    'description' => '完整表名（如 eb_user、user，可带或不带 eb_ 前缀），返回字段与索引详情',
                ],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（keyword / table 至少传一个）
     * @return array 表清单或表结构详情
     * @throws McpException 参数缺失、表名非法或表不存在
     */
    public function execute(array $args): array
    {
        $keyword = trim((string)($args['keyword'] ?? ''));
        $table = trim((string)($args['table'] ?? ''));

        if ($table !== '') {
            return $this->schemaOf($table);
        }
        if ($keyword !== '') {
            return $this->searchTables($keyword);
        }
        throw new McpException('请传 keyword（模糊搜表名）或 table（查具体表结构）');
    }

    /**
     * 模糊搜索表名（information_schema 支持参数绑定，SHOW 语句不支持）
     * @param string $keyword 表名关键词
     * @return array 表清单（含表注释）
     * @throws McpException 关键词非法或无匹配
     */
    private function searchTables(string $keyword): array
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $keyword)) {
            throw new McpException('keyword 仅支持字母 / 数字 / 下划线');
        }
        $prefix = $this->prefix();
        $rows = Db::query(
            'SELECT TABLE_NAME AS `name`, TABLE_COMMENT AS `comment` FROM information_schema.TABLES '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE ? ORDER BY TABLE_NAME LIMIT ' . self::MAX_TABLES,
            ['%' . $keyword . '%']
        );
        $tables = [];
        foreach ($rows as $row) {
            $name = (string)$row['name'];
            $tables[] = [
                'table'      => $name,
                'bare'       => $prefix !== '' && str_starts_with($name, $prefix) ? substr($name, strlen($prefix)) : $name,
                'is_plugin'  => $prefix !== '' && !str_starts_with($name, $prefix),
                'comment'    => (string)$row['comment'],
            ];
        }
        if ($tables === []) {
            throw new McpException("未找到表名含「{$keyword}」的数据表");
        }
        return ['mode' => 'tables', 'keyword' => $keyword, 'count' => count($tables), 'list' => $tables];
    }

    /**
     * 查询单表结构详情（字段 + 索引）
     * @param string $table 表名（可带 / 不带前缀）
     * @return array 字段与索引详情
     * @throws McpException 表名非法或表不存在
     */
    private function schemaOf(string $table): array
    {
        if (!preg_match(self::TABLE_PATTERN, $table)) {
            throw new McpException('table 仅支持字母 / 数字 / 下划线');
        }
        // 前缀解析：短表名优先补前缀（user -> eb_user），命中即用；
        // 未命中的候选（插件表全名无前缀）作为回退
        $prefix = $this->prefix();
        $candidates = str_starts_with($table, $prefix) && $prefix !== ''
            ? [$table]
            : [$prefix . $table, $table];
        $resolved = null;
        foreach ($candidates as $candidate) {
            if ($this->tableExists($candidate)) {
                $resolved = $candidate;
                break;
            }
        }
        if ($resolved === null) {
            throw new McpException("数据表 {$table} 不存在：可用 keyword 参数先搜索表名");
        }

        $columns = [];
        foreach (Db::query('SHOW FULL COLUMNS FROM `' . $resolved . '`') as $column) {
            $columns[] = [
                'field'      => (string)$column['Field'],
                'type'       => (string)$column['Type'],
                'nullable'   => ($column['Null'] ?? 'NO') === 'YES',
                'default'    => $column['Default'],
                'key'        => (string)($column['Key'] ?? ''),
                'extra'      => (string)($column['Extra'] ?? ''),
                'comment'    => (string)($column['Comment'] ?? ''),
            ];
        }

        // 索引聚合：同名字段组归并为一个索引
        $indexes = [];
        foreach (Db::query('SHOW INDEX FROM `' . $resolved . '`') as $index) {
            $name = (string)$index['Key_name'];
            $indexes[$name]['name'] = $name;
            $indexes[$name]['unique'] = (int)$index['Non_unique'] === 0;
            $indexes[$name]['columns'][] = (string)$index['Column_name'];
        }

        return [
            'mode'    => 'schema',
            'table'   => $resolved,
            'comment' => $this->tableComment($resolved),
            'count'   => count($columns),
            'columns' => $columns,
            'indexes' => array_values($indexes),
        ];
    }

    /**
     * 判断表是否存在（information_schema + 参数绑定，避免 SHOW 语句不支持占位符）
     * @param string $table 表名
     * @return bool 是否存在
     */
    private function tableExists(string $table): bool
    {
        $rows = Db::query(
            'SELECT COUNT(*) AS `total` FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$table]
        );
        return (int)($rows[0]['total'] ?? 0) > 0;
    }

    /**
     * 读取表注释
     * @param string $table 表名
     * @return string 表注释
     */
    private function tableComment(string $table): string
    {
        $rows = Db::query('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]);
        return (string)($rows[0]['TABLE_COMMENT'] ?? '');
    }

    /**
     * 获取当前连接的表前缀
     * @return string 前缀（如 eb_）
     */
    private function prefix(): string
    {
        return (string)Db::connect()->getConfig('prefix');
    }
}
