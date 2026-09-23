<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\db\connector;

use PDO;
use think\db\PDOConnection;

/**
 * Pgsql数据库驱动.
 */
class Pgsql extends PDOConnection
{
    /**
     * 默认PDO连接参数.
     *
     * @var array
     */
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * 解析pdo连接的dsn信息.
     *
     * @param array $config 连接信息
     *
     * @return string
     */
    protected function parseDsn(array $config): string
    {
        $dsn = 'pgsql:dbname=' . $config['database'] . ';host=' . $config['hostname'];

        if (!empty($config['hostport'])) {
            $dsn .= ';port=' . $config['hostport'];
        }

        return $dsn;
    }

    /**
     * 取得数据表的字段信息.
     *
     * @param string $tableName
     *
     * @return array
     */
    public function getFields(string $tableName): array
    {
        [$tableName] = explode(' ', $tableName);

        // 分离 schema 和表名
        if (str_contains($tableName, '.')) {
            [$schema, $tableName] = explode('.', $tableName);
        } else {
            $schema = 'public';
        }

        // 使用标准 PostgreSQL 系统表查询字段信息
        $sql = <<<SQL
SELECT
    a.attname AS "field",
    CASE t.typname
        WHEN 'int8' THEN 'bigint'
        WHEN 'int4' THEN 'integer'
        WHEN 'int2' THEN 'smallint'
        WHEN 'bpchar' THEN 'char'
        WHEN 'float4' THEN 'float'
        WHEN 'float8' THEN 'double'
        ELSE t.typname
    END AS "type",
    a.attnotnull AS "notnull",
    (p.contype = 'p') AS "primary",
    pg_get_expr(d.adbin, d.adrelid) AS "default",
    (
        pg_get_serial_sequence(format('%I.%I', n.nspname, c.relname), a.attname) IS NOT NULL
        OR a.attidentity <> ''
    ) AS "autoinc",
    col_description(c.oid, a.attnum) AS "comment"
FROM
    pg_attribute a
    JOIN pg_class c ON a.attrelid = c.oid
    JOIN pg_namespace n ON c.relnamespace = n.oid
    JOIN pg_type t ON a.atttypid = t.oid
    LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
    LEFT JOIN pg_constraint p ON a.attrelid = p.conrelid AND a.attnum = ANY(p.conkey) AND p.contype = 'p'
WHERE
    c.relname = '{$tableName}'
    AND n.nspname = '{$schema}'
    AND a.attnum > 0
    AND NOT a.attisdropped
ORDER BY
    a.attnum
SQL;

        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];

        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $val = array_change_key_case($val);

                $info[$val['field']] = [
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => (bool) $val['notnull'],
                    'default' => $val['default'],
                    'primary' => (bool) $val['primary'],
                    'autoinc' => (bool) $val['autoinc'],
                    'comment' => $val['comment'],
                ];
            }
        }

        return $this->fieldCase($info);
    }

    /**
     * 取得数据库的表信息.
     *
     * @param string $dbName
     *
     * @return array
     */
    public function getTables(string $dbName = ''): array
    {
        $sql    = "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename";
        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);

        return array_column($result, 'tablename');
    }

    protected function supportSavepoint(): bool
    {
        return true;
    }

    /**
     * 获取设置时区的SQL语句.
     *
     * @param string $timezone 时区名称，如 'Asia/Shanghai' 或 '+08:00'
     *
     * @return string
     */
    protected function getSetTimezoneSql(string $timezone): string
    {
        return "SET timezone = '$timezone'";
    }
}
