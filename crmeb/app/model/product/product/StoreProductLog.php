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

namespace app\model\product\product;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductLog extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_log';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'add_time';

    /**
     * 添加时间修改器
     * @return int
     */
    public function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 一对一关联
     * 商品记录关联商品名称
     * @return \think\model\relation\HasOne
     */
    public function storeName()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->bind([
            'store_name',
            'image',
            'product_price'=>'price',
            'stock',
            'is_show'
        ]);
    }

    /**
     * 记录类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value != '') $query->where('type', $value);
    }

    /**
     * 商品ID搜索器
     * @param $query
     * @param $value
     */
    public function searchProductIdAttr($query, $value)
    {
        if ($value != '') $query->where('product_id', $value);
    }
    /**
     * 用户ID搜索器
     * @param $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value != '') $query->where('uid', $value);
    }
}
