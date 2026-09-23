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

namespace app\dao\system\store;


use crmeb\basic\BaseDao;
use app\model\system\store\SystemStore;
use think\db\Raw;

/**
 * 门店dao
 * Class SystemStoreDao
 * @package app\dao\system\store
 */
class SystemStoreDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemStore::class;
    }

    /**
     * 经纬度排序计算
     * @param string $latitude
     * @param string $longitude
     * @return Raw|null
     */
    public function distance(string $latitude, string $longitude)
    {
        $latitude = $this->filterCoordinate($latitude, -90, 90);
        $longitude = $this->filterCoordinate($longitude, -180, 180);
        if ($latitude === '' || $longitude === '') {
            return null;
        }
        return new Raw(
            '(round(6367000 * 2 * asin(sqrt(pow(sin(((latitude * pi()) / 180 - (? * pi()) / 180) / 2), 2) + cos((? * pi()) / 180) * cos((latitude * pi()) / 180) * pow(sin(((longitude * pi()) / 180 - (? * pi()) / 180) / 2), 2))))) AS distance',
            [$latitude, $latitude, $longitude]
        );
    }

    /**
     * 获取
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $latitude
     * @param string $longitude
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreList(array $where, array $field, int $page = 0, int $limit = 0, string $latitude = '', string $longitude = '')
    {
        $query = $this->search($where);
        $distance = $this->distance($latitude, $longitude);
        if ($distance) {
            $field[] = $distance;
            $query->order('distance ASC');
        }
        return $query->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->field($field)->order('id desc')->select()->toArray();
    }

    /**
     * 过滤经纬度参数
     * @param string $coordinate
     * @param float $min
     * @param float $max
     * @return string
     */
    private function filterCoordinate(string $coordinate, float $min, float $max): string
    {
        $coordinate = trim($coordinate);
        if ($coordinate === '' || !is_numeric($coordinate)) {
            return '';
        }
        $coordinate = (float)$coordinate;
        if ($coordinate < $min || $coordinate > $max) {
            return '';
        }
        return rtrim(rtrim(sprintf('%.8F', $coordinate), '0'), '.');
    }

    /**
     * 获取门店不分页
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStore(array $where)
    {
        return $this->search($where)->order('add_time DESC')->field(['id', 'name'])->select()->toArray();
    }
}
