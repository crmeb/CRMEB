<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
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
 * Sqlsrv数据库驱动.
 */
class Sqlsrv extends PDOConnection
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
        $dsn = 'sqlsrv:Database=' . $config['database'] . ';Server=' . $config['hostname'];

        if (!empty($config['hostport'])) {
            $dsn .= ',' . $config['hostport'];
        }

        if (!empty($config['trust_server_certificate'])) {
            $dsn .= ';TrustServerCertificate=' . $config['trust_server_certificate'];
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
        str_contains($tableName, '.') && $tableName = substr($tableName, strpos($tableName, '.') + 1);

        $sql    = "SELECT   column_name,   data_type,   column_default,   is_nullable
            FROM    information_schema.tables AS t
            JOIN    information_schema.columns AS c
            ON  t.table_catalog = c.table_catalog
            AND t.table_schema  = c.table_schema
            AND t.table_name    = c.table_name
            WHERE   t.table_name = '$tableName'";
        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];

        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $val = array_change_key_case($val);

                $info[$val['column_name']] = [
                    'name'    => $val['column_name'],
                    'type'    => $val['data_type'],
                    'notnull' => (bool) ('' === $val['is_nullable']), // not null is empty, null is yes
                    'default' => $val['column_default'],
                    'primary' => false,
                    'autoinc' => false,
                ];
            }
        }

        $sql    = "SELECT column_name FROM information_schema.key_column_usage WHERE table_name='$tableName'";
        $pdo    = $this->linkID->query($sql);
        $result = $pdo->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $info[$result['column_name']]['primary'] = true;
        }

        return $this->fieldCase($info);
    }

    /**
     * 取得数据表的字段信息.
     *
     * @param string $dbName
     *
     * @return array
     */
    public function getTables(string $dbName = ''): array
    {
        $sql = "SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_TYPE = 'BASE TABLE'
            ";

        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];

        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }

        return $info;
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
        // SQL Server 使用 SET TIME ZONE 命令
        // 注意：SQL Server 2016 及以上版本才支持此命令
        return "SET TIME ZONE '$timezone'";
    }
}
