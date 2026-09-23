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

namespace app\services\product\product;


use app\dao\product\product\StoreDescriptionDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * Class StoreDescriptionService
 * @package app\services\product\product
 * @method value($where, ?string $field = null) 获取字段
 */
class StoreDescriptionServices extends BaseService
{
     /**
       * @var StoreDescriptionDao
       */
    protected $dao;

     /**
     * StoreDescriptionServices constructor.
     * @param StoreDescriptionDao $dao
     */
    public function __construct(StoreDescriptionDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取商品详情
     * @param array $where
     * @return string
     */
    public function getDescription(array $where)
    {
        $info = $this->dao->getDescription($where);
        if ($info) return htmlspecialchars_decode($info->description);
        return '';
    }

    /**
     * 保存商品详情
     * @param int $id 商品/活动ID
     * @param string $description 商品详情内容
     * @param string $activityType 活动/交易模式类型（normal/integral/seckill/combination/bargain）
     * @return bool
     */
    public function saveDescription(int $id, string $description, string $activityType = 'normal')
    {
        $description = htmlspecialchars($description);
        $where = ['product_id' => $id, 'activity_type' => $activityType];
        $info = $this->dao->count($where);
        if ($info) {
            $res = $this->dao->update($where, ['description' => $description]);
        } else {
            $res = $this->dao->save($where + ['description' => $description]);
        }
        if (!$res) throw new AdminException('商品详情保存失败');
    }

}
