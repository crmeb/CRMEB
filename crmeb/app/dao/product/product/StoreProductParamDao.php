<?php

namespace app\dao\product\product;

use app\dao\BaseDao;
use app\model\product\product\StoreProductParam;

/**
 * 商品参数
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/12/17
 */
class StoreProductParamDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    protected function setModel(): string
    {
        return StoreProductParam::class;
    }

    /**
     * 条件搜索
     * @param $where
     * @return \crmeb\basic\BaseModel
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function conditionSearch($where)
    {
        return $this->getModel()
            ->when(isset($where['name']) && $where['name'] !== '', function ($query) use ($where) {
                $query->whereLike('name', '%' . $where['name'] . '%');
            })->when(isset($where['is_del']) && $where['is_del'] !== '', function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', $where['status']);
            });
    }

    /**
     * 获取参数列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamList(array $where = [], string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->conditionSearch($where)->field($field)->order('sort DESC')->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取参数列表数量
     * @param array $where
     * @return int
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamCount(array $where = [])
    {
        return $this->conditionSearch($where)->count();
    }
}
