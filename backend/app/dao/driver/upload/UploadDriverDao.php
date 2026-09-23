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

namespace app\dao\driver\upload;

use crmeb\basic\BaseDao;
use app\model\driver\upload\UploadDriver;

/**
 * Class UploadDriverDao
 * @package app\dao\driver\upload
 */
class UploadDriverDao extends BaseDao
{

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return UploadDriver::class;
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
                // 支持 'sort desc' 或 'sort asc' 格式
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
     * 禁用除指定ID外的所有驱动
     * @param int $exceptId
     * @return int
     */
    public function disableAllExcept(int $exceptId)
    {
        return $this->getModel()->where('driver_type', UploadDriver::DRIVER_TYPE)
            ->where('owner_plugin_id', '<>', '')->where('id', '<>', $exceptId)->where('status', 1)
            ->update(['status' => 0, 'update_time' => time()]);
    }
}
