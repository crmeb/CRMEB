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

namespace crmeb\traits;

use app\dao\BaseDao;

/**
 * 基础查询
 * Trait SearchDaoTrait
 * @package crmeb\traits
 * @mixin BaseDao
 */
trait SearchDaoTrait
{

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
                $query->order($sort, 'desc');
            }
        })->with($with)->select()->toArray();
    }
}
