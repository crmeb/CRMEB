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

namespace app\services\product\product;


use app\dao\product\product\StoreVisitDao;
use app\services\BaseServices;

/**
 * Class StoreVisitService
 * @package app\services\product\product
 * @method getSum($where,$field)
 * @method getDistinctCount(array $where, $field, $search = true)
 * @method getProductTrend($time, $timeType, $str) 商品趋势
 */
class StoreVisitServices extends BaseServices
{
    public function __construct(StoreVisitDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     *  设置浏览信息
     * @param $uid
     * @param int $product_id
     * @param int $cate
     * @param string $type
     * @param string $content
     * @param int $min
     */
    public function setView($uid, $product_id = 0, $product_type = 'product', $cate = 0, $type = '', $content = '', $min = 20)
    {

        $view = $this->dao->getOne(['uid' => $uid, 'product_id' => $product_id, 'product_type' => $product_type], 'count,add_time,id');
        if ($view && $type != 'search') {
            $time = time();
            if (($view['add_time'] + $min) < $time) {
                $this->dao->update($view['id'], ['count' => $view['count'] + 1, 'add_time' => time()]);
            }
        } else {
            $cate = explode(',', $cate)[0];
            $this->dao->save([
                'add_time' => time(),
                'count' => 1,
                'product_id' => $product_id,
                'product_type' => $product_type,
                'cate_id' => $cate,
                'type' => $type,
                'uid' => $uid,
                'content' => $content
            ]);
        }
    }
}
