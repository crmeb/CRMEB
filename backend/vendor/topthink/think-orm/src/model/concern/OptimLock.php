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

namespace think\model\concern;

use think\db\exception\DbException as Exception;

/**
 * 乐观锁
 */
trait OptimLock
{
    protected function getOptimLockField()
    {
        return $this->getOption('optimLock') ?? 'lock_version';
    }

    /**
     * 数据检查.
     * @param array $data 数据
     * @param bool  $isUpdate 是否更新
     * @return void
     */
    protected function checkData(array &$data, bool $isUpdate): void
    {
        $isUpdate ? $this->updateLockVersion($data) : $this->recordLockVersion($data);
    }

    /**
     * 记录乐观锁
     *
     * @param array $data 数据
     * @return void
     */
    protected function recordLockVersion(array &$data): void
    {
        $optimLock = $this->getOptimLockField();

        $this->setData($optimLock, 0);
        $data[$optimLock] = 0;
    }

    /**
     * 更新乐观锁
     *
     * @param array $data 数据
     * @return void
     */
    protected function updateLockVersion(array &$data): void
    {
        $optimLock = $this->getOptimLockField();
        $lockVer   = $this->getOrigin($optimLock);

        $this->setData($optimLock, $lockVer + 1);
        $data[$optimLock] = $lockVer + 1;
    }

    public function getDbWhere($where = [])
    {
        $db = $this->db();
        // 检查条件
        if (!empty($where)) {
            $db->where($where);
        }
        $optimLock  = $this->getOptimLockField();
        $lockVer    = $this->getOrigin($optimLock);
        $pk         = $this->getPk();
        if (is_array($pk)) {
            $db->where($this->getKey());
        } else {
            $db->where($pk, '=', $this->getKey());
        }
        $db->where($optimLock, '=', $lockVer);

        return $db;
    }
}
