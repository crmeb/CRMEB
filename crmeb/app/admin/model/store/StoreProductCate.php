<?php
declare (strict_types = 1);

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use think\Model;

/**
 * @mixin think\Model
 */
class StoreProductCate extends Model
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_cate';

    use ModelTrait;
}
