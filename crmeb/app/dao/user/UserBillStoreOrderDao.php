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
use app\model\order\StoreOrder;
use app\model\user\UserBill;

/**
 *
 * Class UserBillStoreOrderDao
 * @package app\dao\user
 */
class UserBillStoreOrderDao extends BaseDao
{

    protected $alias = '';
    protected $join_alis = '';

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserBill::class;
    }

    public function joinModel(): string
    {
        return StoreOrder::class;
    }

    /**
     * 关联模型
     * @param string $alias
     * @param string $join_alias
     * @return \crmeb\basic\BaseModel
     */
    public function getModel(string $table = '', string $alias = 'b', string $join_alias = 'o', $join = 'left')
    {
        $this->alias = $alias;
        $this->join_alis = $join_alias;
        if (!$table) {
            /** @var StoreOrder $storeOrder */
            $storeOrder = app()->make($this->joinModel());
            $table = $storeOrder->getName();
        }
        return parent::getModel()->join($table . ' ' . $join_alias, $alias . '.link_id = ' . $join_alias . '.id', $join)->alias($alias);
    }

    /**
     * 时间分组
     * @param array $where
     * @param array $whereOr
     * @param string $field
     * @param string $group
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getList(array $where, array $whereOr, array $times, string $field, $page, $limit)
    {
        return $this->getModel()->where($where)->where("FROM_UNIXTIME(b.add_time, '%Y-%m')", 'in', $times)
            ->where(function ($q) use ($whereOr) {
                $q->whereOr($whereOr);
            })
            ->with([
                'user' => function ($query) {
                    $query->field('uid,avatar,nickname')->bind(['avatar' => 'avatar', 'nickname' => 'nickname']);
                }])->field($field)->order('id desc')->page($page, $limit)->select()->toArray();
    }

    /**
     * 时间分组
     * @param array $where
     * @param array $whereOr
     * @param string $field
     * @param string $group
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getListByGroup(array $where, array $whereOr, string $field, string $group, $page, $limit)
    {
        return $this->getModel()->where($where)->where(function ($q) use ($whereOr) {
            $q->whereOr($whereOr);
        })->field($field)->order($group . ' desc')->group($group)->page($page, $limit)->select()->toArray();
    }

    /**
     * 时间分组
     * @param array $where
     * @param array $whereOr
     * @param string $field
     * @param string $group
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getListCount(array $where, array $whereOr)
    {
        return $this->getModel()->where($where)->where(function ($q) use ($whereOr) {
            $q->whereOr($whereOr);
        })->count('b.id');
    }
}
