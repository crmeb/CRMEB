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
declare (strict_types=1);

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\UserLabel;

/**
 *
 * Class UserLabelDao
 * @package app\dao\user
 */
class UserLabelDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserLabel::class;
    }

    /**
     * 获取列表
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param array $where
     * @param array $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(int $page = 0, int $limit = 0, array $where = [], array $field = ['*']): array
    {
        return $this->search($where)->with(['cateName'])->when(isset($where['label_cate']) && $where['label_cate'], function ($query) use ($where) {
            $query->where('label_cate', $where['label_cate']);
        })->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->field($field)->select()->toArray();
    }
}
