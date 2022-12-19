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

namespace app\model\activity\live;

use app\model\product\product\StoreProduct;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class LiveGoods
 * @package app\model\live
 */
class LiveGoods extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'live_goods';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'add_time';

    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    public function getAddTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }


    public function product()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id');
    }

    /**
     * 微信商品id
     * @param Model $query
     * @param $value
     */
    public function searchGoodIdAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('goods_id', $value);
        else
            $query->where('good_id', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchKerwordAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('id|goods_id|product_id|name', "%{$value}%");
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsShowAttr($query, $value)
    {
        if ($value !== '') $query->where('is_show', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            switch ($value) {
                case 1:
                    $query->where('audit_status', 2);
                    break;
                case -1:
                    $query->where('audit_status', 3);
                    break;
                case 0:
                    $query->whereIn('audit_status', [0, 1]);
                    break;
            }
        }
    }

    public function searchLiveIdAttr($query, $value)
    {
        if ($value > 0) {
            $query->whereNotIn('id', function ($query) use ($value) {
                $query->name('live_room_goods')->where('live_room_id', $value)->field('live_goods_id')->select();
            });
        }
    }
}
