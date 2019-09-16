<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\db\connector;

use PDO;
use think\db\PDOConnection;

/**
 * mysql数据库驱动
 */
class Mysql extends PDOConnection
{

    /**
     * 数据库连接参数配置
     * @var array
     */
    protected $config = [
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'hostname'        => '127.0.0.1',
        // 数据库名
        'database'        => '',
        // 用户名
        'username'        => '',
        // 密码
        'password'        => '',
        // 端口
        'hostport'        => '',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
        // 数据库调试模式
        'debug'           => false,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 模型写入后自动读取主服务器
        'read_master'     => false,
        // 是否严格检查字段是否存在
        'fields_strict'   => true,
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
        // Builder类
        'builder'         => '',
        // Query类
        'query'           => '',
        // 是否需要断线重连
        'break_reconnect' => false,
        // 断线标识字符串
        'break_match_str' => [],
    ];

    /**
     * 解析pdo连接的dsn信息
     * @access protected
     * @param  array $config 连接信息
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
     * 取得数据表的字段信息
     * @access public
     * @param  string $tableName
     * @return array
     */
    public function getFields(string $tableName): array
    {
        list($tableName) = explode(' ', $tableName);

        if (false === strpos($tableName, '`')) {
            if (strpos($tableName, '.')) {
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
                    'notnull' => (bool) ('' === $val['null']), // not null is empty, null is yes
                    'default' => $val['default'],
                    'primary' => (strtolower($val['key']) == 'pri'),
                    'autoinc' => (strtolower($val['extra']) == 'auto_increment'),
                    'comment' => $val['comment'],
                ];
            }
        }

        return $this->fieldCase($info);
    }

    /**
     * 取得数据库的表信息
     * @access public
     * @param  string $dbName
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

    /**
     * SQL性能分析
     * @access protected
     * @param  string $sql
     * @return array
     */
    protected function getExplain(string $sql): array
    {
        $pdo    = $this->linkID->query("EXPLAIN " . $sql);
        $result = $pdo->fetch(PDO::FETCH_ASSOC);
        $result = array_change_key_case($result);

        if (isset($result['extra'])) {
            if (strpos($result['extra'], 'filesort') || strpos($result['extra'], 'temporary')) {
                $this->log('SQL:' . $this->queryStr . '[' . $result['extra'] . ']', 'warn');
            }
        }

        return $result;
    }

    protected function supportSavepoint(): bool
    {
        return true;
    }

}
