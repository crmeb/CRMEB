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
 * mysql数据库驱动.
 */
class Mysql extends PDOConnection
{
    /**
     * 解析pdo连接的dsn信息.
     *
     * @param array $config 连接信息
     *
     * @return string
     */
    protected function parseDsn(array $config): string
    {
        if (!empty($config['socket'])) {
            $dsn = 'mysql:unix_socket=' . $config['socket'];
        } elseif (!empty($config['hostport'])) {
            $dsn = 'mysql:host=' . $config['hostname'] . ';port=' . $config['hostport'];
        } else {
            $dsn = 'mysql:host=' . $config['hostname'];
        }
        $dsn .= ';dbname=' . $config['database'];

        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
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

        if (!str_contains($tableName, '`')) {
            if (str_contains($tableName, '.')) {
                $tableName = str_replace('.', '`.`', $tableName);
            }
            $tableName = '`' . $tableName . '`';
        }

        $sql    = 'SHOW FULL COLUMNS FROM ' . $tableName;
        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];

        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $val = array_change_key_case($val);

                $info[$val['field']] = [
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => 'NO' == $val['null'],
                    'default' => $val['default'],
                    'primary' => strtolower($val['key']) == 'pri',
                    'autoinc' => strtolower($val['extra']) == 'auto_increment',
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
        $sql    = !empty($dbName) ? 'SHOW TABLES FROM ' . $dbName : 'SHOW TABLES ';
        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];

        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }

        return $info;
    }

    protected function supportSavepoint(): bool
    {
        return true;
    }

    /**
     * 启动XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function startTransXa(string $xid): void
    {
        $this->initConnect(true);
        $this->linkID->exec("XA START '$xid'");
    }

    /**
     * 预编译XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function prepareXa(string $xid): void
    {
        $this->initConnect(true);
        $this->linkID->exec("XA END '$xid'");
        $this->linkID->exec("XA PREPARE '$xid'");
    }

    /**
     * 提交XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function commitXa(string $xid): void
    {
        $this->initConnect(true);
        $this->linkID->exec("XA COMMIT '$xid'");
    }

    /**
     * 回滚XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function rollbackXa(string $xid): void
    {
        $this->initConnect(true);
        $this->linkID->exec("XA ROLLBACK '$xid'");
    }

    /**
     * 使用无缓冲游标查询记录（MySQL专用）
     *
     * @param \think\db\BaseQuery $query 查询对象
     * @param string $sql 查询SQL
     *
     * @return \Generator
     */
    protected function cursorUnbuffered(\think\db\BaseQuery $query, string $sql)
    {
        // 启用无缓冲查询模式
        $this->initUnbufferedMode();
        
        try {
            // 执行查询操作
            yield from $this->getCursor($query, $sql, $query->getModel());
        } finally {
            // 恢复缓冲模式
            $this->restoreBufferedMode();
        }
    }

    /**
     * 开启无缓冲查询模式
     *
     * @return void
     */
    protected function initUnbufferedMode(): void
    {
        if ($this->linkID) {
            $this->linkID->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        }
    }

    /**
     * 恢复缓冲查询模式
     *
     * @return void
     */
    protected function restoreBufferedMode(): void
    {
        if ($this->linkID) {
            $this->linkID->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
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
        return "SET time_zone = '$timezone'";
    }
}
