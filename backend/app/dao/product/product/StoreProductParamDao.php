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
namespace app\dao\product\product;

use crmeb\basic\BaseDao;
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
            })->when(isset($where['is_del']) && $where['is_del'] !== '' && $where['is_del'] !== null, function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            })->when(isset($where['status']) && $where['status'] !== '' && $where['status'] !== null, function ($query) use ($where) {
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
