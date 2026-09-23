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
declare (strict_types=1);

namespace app\dao\product\product;

use crmeb\basic\BaseDao;
use app\model\product\CenterProductModel;
use app\model\product\product\StoreProductReply;

/**
 * 商品评论与商品中台关联 DAO
 * 从 eb_store_product_reply 关联 eb_product_center 查询评论数据
 *
 * Class StoreProductReplyCenterProductDao
 * @package app\dao\product\product
 */
class StoreProductReplyCenterProductDao extends BaseDao
{
    /**
     * 表别名
     * @var string
     */
    protected $alias = '';

    /**
     * 链表别名
     * @var string
     */
    protected $joinAlias = '';

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductReply::class;
    }

    /**
     * 链表模型
     * @return string
     */
    public function setJoinModel(): string
    {
        return CenterProductModel::class;
    }

    /**
     * 关联模型
     * @param string $alias
     * @param string $join_alias
     * @return \crmeb\basic\BaseModel
     */
    public function getModel(string $alias = 'r', string $join_alias = 'p', $join = 'left')
    {
        $this->alias = $alias;
        $this->joinAlis = $join_alias;
        /** @var CenterProductModel $storeProduct */
        $storeProduct = app()->make($this->setJoinModel());
        $table = $storeProduct->getName();
        return parent::getModel()->join($table . ' ' . $join_alias, $alias . '.product_id = ' . $join_alias . '.id', $join)->alias($alias);
    }

    /**
     * 获取评论列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductReplyList(array $where, int $page, int $limit)
    {
        return $this->searchWhere($where)->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取评论条数
     * @param array $where
     * @return int
     */
    public function replyCount(array $where)
    {
        return $this->searchWhere($where)->count();
    }

    /**
     * 搜索
     * @param array $where
     * @return \crmeb\basic\BaseModel
     */
    public function searchWhere(array $where = [])
    {
        $model = $this->getModel()->where('r.is_del', 0)->withSearch(['time'], ['time' => $where['data'], 'timeKey' => 'r.add_time'])->field('r.*,p.name as store_name,p.image,r.nickname as account,SUM(r.product_score+r.service_score) as score')->group('id');
        if ($where['is_reply'] !== '' && $where['is_reply'] !== null) $model = $model->where('r.is_reply', $where['is_reply']);
        if ($where['product_id']) $model = $model->where('r.product_id', $where['product_id']);
        if ($where['store_name']) $model = $model->where('p.name|r.product_id', 'Like', '%' . $where['store_name'] . '%');
        if ($where['account']) $model = $model->where('r.nickname', 'LIKE', '%' . $where['account'] . '%');
        if ($where['status'] !== '') $model = $model->where('status', $where['status']);
        if ($where['key'] !== '') {
            $model = $model->order($where['key'], $where['order']);
        } else {
            $model = $model->order('r.add_time desc,r.is_reply asc');
        }
        return $model;
    }

}
