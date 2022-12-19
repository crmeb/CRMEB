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
namespace app\model\product\sku;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class StoreProductAttrResult
 * @package app\common\model\product
 */
class StoreProductAttrResult extends BaseModel
{

    use ModelTrait;

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_result';

    protected $insert = ['change_time'];

    /**
     * 自动增加改变时间
     * @param $value
     * @return int
     */
    protected static function setChangeTimeAttr($value)
    {
        return time();
    }

    /**
     * 数据json化
     * @param $value
     * @return false|string
     */
    protected static function setResultAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    /**
     * 商品搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchProductIdAttr($query, $value)
    {
        $query->where('product_id', $value);
    }

    /**
     * 商品类型搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value)
    {
        $query->where('type', $value);
    }
}
