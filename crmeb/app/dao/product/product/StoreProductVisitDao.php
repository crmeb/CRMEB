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

namespace app\dao\product\product;


use app\dao\BaseDao;
use app\model\product\product\StoreProduct;
use app\model\product\product\StoreVisit;

/**
 * Class StoreProductVisitDao
 * @package app\dao\product\product
 */
class StoreProductVisitDao extends BaseDao
{

    /**
     * 主表别名
     * @var string
     */
    protected $alias = 'a';

    /**
     * 附表别名
     * @var string
     */
    protected $joinAlis = 'c';

    /**
     * 主表模型设置
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProduct::class;
    }

    /**
     * 连表模型设置
     * @return string
     */
    protected function setJoinModel(): string
    {
        return StoreVisit::class;
    }

    /**
     * 设置模型
     * @return \crmeb\basic\BaseModel
     */
    protected function getModel()
    {
        $name = app()->make($this->setJoinModel())->getName();
        return parent::getModel()->alias($this->alias)->join($name . ' ' . $this->joinAlis, $this->alias . '.id = ' . $this->joinAlis . '.product_id');
    }

    /**
     * 用户浏览商品足记
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserVisitProductList(array $where, int $page, int $limit)
    {
        return $this->getModel()->when(isset($where['uid']), function ($query) use ($where) {
            $query->where($this->joinAlis . '.uid', $where['uid']);
        })->when($page, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when(isset($where['store_name']), function ($query) use ($where) {
            $query->whereLike($this->alias . '.store_name', '%' . $where['store_name'] . '%');
        })->where(['is_del' => 0, 'is_show' => 1])->field([
            $this->alias . '.store_name',
            $this->alias . '.image',
            $this->alias . '.price',
            'IFNULL(' . $this->alias . '.sales,0) + IFNULL(' . $this->alias . '.ficti,0) as sales',
            $this->alias . '.stock',
            $this->alias . '.id'
        ])->order($this->alias . '.sort DESC,' . $this->alias . '.id DESC')->select()->toArray();
    }
}
