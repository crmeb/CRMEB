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

namespace app\dao\driver\logistics;

use crmeb\basic\BaseDao;
use app\model\driver\logistics\LogisticsDriver;

/**
 * 物流查询驱动数据访问层
 * Class LogisticsDriverDao
 * @package app\dao\driver\logistics
 */
class LogisticsDriverDao extends BaseDao
{

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return LogisticsDriver::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param array|string[] $field
     * @param int $page
     * @param int $limit
     * @param null $sort
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where = [], array $field = ['*'], int $page = 0, int $limit = 0, $sort = null, array $with = [])
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when($sort, function ($query) use ($sort) {
            if (is_array($sort)) {
                foreach ($sort as $v => $k) {
                    if (is_numeric($v)) {
                        $query->order($k, 'desc');
                    } else {
                        $query->order($v, $k);
                    }
                }
            } else {
                $query->order($sort);
            }
        })->with($with)->select()->toArray();
    }

    /**
     * 根据 driver_key 获取驱动信息
     * @param string $driverKey
     * @return array|\think\Model|null
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getByDriverKey(string $driverKey)
    {
        return $this->search(['driver_key' => $driverKey])->find();
    }

    /**
     * 获取所有启用的驱动
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getEnabledDrivers()
    {
        return $this->search(['status' => 1])->order('sort', 'desc')->select()->toArray();
    }

    /**
     * 获取当前启用的物流查询驱动所需字段
     * @return array|null
     */
    public function getEnabledDriver(): ?array
    {
        $driver = $this->search(['status' => 1])
            ->field(['driver_key', 'owner_plugin_id', 'config_fields'])
            ->order('sort', 'desc')
            ->find();
        return $driver ? $driver->toArray() : null;
    }

    /**
     * 禁用除指定ID外的所有驱动
     * @param int $exceptId
     * @return int
     */
    public function disableAllExcept(int $exceptId)
    {
        return $this->getModel()->where('id', '<>', $exceptId)->update(['status' => 0, 'update_time' => time()]);
    }
}
