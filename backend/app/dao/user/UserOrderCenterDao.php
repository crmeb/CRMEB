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
declare(strict_types=1);

namespace app\dao\user;

use crmeb\basic\BaseDao;
use app\model\user\User;

/**
 * 用户与订单中台关联 DAO
 * 替代旧版 UserStoreOrderDao，关联 eb_user 与 eb_order_center 表
 */
class UserOrderCenterDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return User::class;
    }

    /**
     * 获取用户推广统计列表
     * 关联订单中台表统计每个用户的订单数和商品数
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param string $orderBy 排序方式
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getUserSpreadCountList(array $where, string $field, string $orderBy, int $page, int $limit): array
    {
        return $this->getModel()
            ->alias('u')
            ->field($field)
            ->join('(SELECT uid, COUNT(*) as orderCount, SUM(total_num) as numberCount FROM eb_order_center WHERE is_del = 0 AND pay_status = 1 GROUP BY uid) p', 'p.uid = u.uid', 'LEFT')
            ->where($where)
            ->order($orderBy)
            ->page($page, $limit)
            ->select()
            ->toArray();
    }
}
