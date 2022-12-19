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

namespace app\services\product\product;

use app\services\BaseServices;
use app\dao\product\product\StoreProductReplyStoreProductDao;

/**
 *
 * Class StoreProductReplyStoreProductServices
 * @package app\services\product\product
 */
class StoreProductReplyStoreProductServices extends BaseServices
{

    /**
     * StoreProductReplyStoreProductServices constructor.
     * @param StoreProductReplyStoreProductDao $dao
     */
    public function __construct(StoreProductReplyStoreProductDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取评论列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductReplyList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getProductReplyList($where, $page, $limit);
        $count = $this->dao->replyCount($where + ['is_del' => 0]);
        return compact('list', 'count');
    }
}
