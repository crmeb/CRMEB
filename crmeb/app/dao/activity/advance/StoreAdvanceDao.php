<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\dao\activity\advance;

use app\dao\BaseDao;
use app\model\activity\advance\StoreAdvance;

/**
 * 预售商品
 * Class StoreAdvanceDao
 * @package app\dao\activity
 */
class StoreAdvanceDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreAdvance::class;
    }

    /**
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0)
    {
        return $this->search($where)
            ->when($where['time_type'], function ($query) use ($where) {
                if ($where['time_type'] == 1) $query->whereTime('start_time', '>', time());
                if ($where['time_type'] == 2) $query->whereTime('start_time', '<=', time())->whereTime('stop_time', '>=', time());
                if ($where['time_type'] == 3) $query->whereTime('stop_time', '<', time());
            })
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->with(['product'])->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->search($where)
            ->when($where['time_type'], function ($query) use ($where) {
                if ($where['time_type'] == 1) $query->whereTime('start_time', '>', time());
                if ($where['time_type'] == 2) $query->whereTime('start_time', '<=', time())->whereTime('stop_time', '>=', time());
                if ($where['time_type'] == 3) $query->whereTime('stop_time', '<', time());
            })->count();
    }


    /**
     * 获取预售商品是否开启
     * @param array $ids
     * @return int
     */
    public function getAdvanceStatus(array $ids)
    {
        return $this->getModel()->whereIn('product_id', $ids)->where('is_del', 0)->where('status', 1)->count();
    }
}
