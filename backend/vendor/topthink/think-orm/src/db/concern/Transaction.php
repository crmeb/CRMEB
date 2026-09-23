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

namespace think\db\concern;

/**
 * 事务支持
 */
trait Transaction
{
    /**
     * 执行数据库Xa事务
     *
     * @param callable $callback 数据操作方法回调
     * @param array    $dbs      多个查询对象或者连接对象
     *
     * @throws \PDOException
     * @throws \Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function transactionXa(callable $callback, array $dbs = [])
    {
        return $this->connection->transactionXa($callback, $dbs);
    }

    /**
     * 执行数据库事务
     *
     * @param callable $callback 数据操作方法回调
     *
     * @return mixed
     */
    public function transaction(callable $callback)
    {
        return $this->connection->transaction($callback);
    }

    /**
     * 启动事务
     *
     * @return void
     */
    public function startTrans(): void
    {
        $this->connection->startTrans();
    }

    /**
     * 用于非自动提交状态下面的查询提交.
     *
     * @throws \PDOException
     *
     * @return void
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * 事务回滚.
     *
     * @throws \PDOException
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->connection->rollback();
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
        $this->connection->startTransXa($xid);
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
        $this->connection->prepareXa($xid);
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
        $this->connection->commitXa($xid);
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
        $this->connection->rollbackXa($xid);
    }
}
