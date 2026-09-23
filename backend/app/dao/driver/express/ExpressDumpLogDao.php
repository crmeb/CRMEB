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

namespace app\dao\driver\express;

use crmeb\basic\BaseDao;
use app\model\driver\express\ExpressDumpLog;

/**
 * 电子面单打印记录数据访问层
 * Class ExpressDumpLogDao
 * @package app\dao\driver\express
 */
class ExpressDumpLogDao extends BaseDao
{
    /**
     * @return string
     */
    protected function setModel(): string
    {
        return ExpressDumpLog::class;
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
            $query->order($sort);
        })->with($with)->select()->toArray();
    }
}
