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

namespace app\dao\activity\coupon;

use app\dao\BaseDao;
use app\model\activity\coupon\StoreCouponUser;
use app\model\user\User;

/**
 *
 * Class StoreCouponUserUserDao
 * @package app\dao\coupon
 */
class StoreCouponUserUserDao extends BaseDao
{
    protected $alias = '';
    protected $join_alis = '';

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreCouponUser::class;
    }

    /**
     * 连表模型
     * @return string
     */
    public function joinModel(): string
    {
        return User::class;
    }

    /**
     * 关联模型
     * @param string $alias
     * @param string $join_alias
     * @return \crmeb\basic\BaseModel
     */
    public function getModel(string $alias = 'c', string $join_alias = 'u', $join = 'left')
    {
        $this->alias = $alias;
        $this->join_alis = $join_alias;
        /** @var User $user */
        $user = app()->make($this->joinModel());
        $table = $user->getName();
        return parent::getModel()->join($table . ' ' . $join_alias, $alias . '.uid = ' . $join_alias . '.uid', $join)->alias($alias);
    }

    /**
     * 列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sysPage(array $where, int $page, int $limit)
    {
        return $this->searchWhere($where)->page($page, $limit)->order('id desc')->select()->toArray();
    }

    /**
     * 总数
     * @param array $where
     * @return int
     */
    public function sysCount(array $where)
    {
        return $this->searchWhere($where)->count();
    }

    /**
     * 筛选条件
     * @param array $where
     * @return \crmeb\basic\BaseModel
     */
    public function searchWhere(array $where = [])
    {
        return $this->getModel()
            ->when($where['nickname'] != '', function ($query) use ($where) {
                $query->where('u.nickname', 'like', '%' . $where['nickname'] . '%');
            })->when($where['status'] != '', function ($query) use ($where) {
                $query->where('c.status', $where['status']);
            })->when($where['coupon_title'] != '', function ($query) use ($where) {
                $query->where('c.coupon_title', 'like', '%' . $where['coupon_title'] . '%');
            })->field('c.*,u.nickname');
    }
}
