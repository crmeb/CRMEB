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

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\order\StoreOrder;
use app\model\user\User;

/**
 *
 * Class UserStoreOrderDao
 * @package app\dao\user
 */
class UserStoreOrderDao extends BaseDao
{
    /**
     * @var string
     */
    protected $alias = '';

    /**
     * @var string
     */
    protected $join_alis = '';

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return User::class;
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
    public function getModel(string $table = '', string $alias = 'u', string $join_alias = 'p', $join = 'left')
    {
        $this->alias = $alias;
        $this->join_alis = $join_alias;
        if (!$table) {
            /** @var StoreOrder $storeOrder */
            $storeOrder = app()->make($this->joinModel());
            $table = $storeOrder->getName();
        }
        return parent::getModel()->join($table . ' ' . $join_alias, $alias . '.uid = ' . $join_alias . '.uid', $join)->alias($alias);
    }

    /**
     * 推广团队列表
     * @param array $where
     * @param string $field
     * @param string $order_by
     * @param $page
     * @param $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserSpreadCountList(array $where, string $field, string $order_by, int $page, int $limit)
    {
        $table = app()->make($this->joinModel())->getModel()->where('o.paid', 1)->whereIn('o.pid', [-1, 0])->group('o.uid')->field(['SUM(o.pay_price) as numberCount', 'count(o.id) as orderCount', 'o.uid', 'o.order_id'])
            ->where('o.refund_status', 0)->alias('o')->fetchSql(true)->select();

        return $this->getModel('(' . $table . ')')->where($where)->field($field)->order($order_by)->page($page, $limit)->select()->toArray();
    }
}
