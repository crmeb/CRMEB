<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\order;


use app\model\user\UserInvoice;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class StoreOrderInvoice
 * @package app\model\order
 */
class StoreOrderInvoice extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'store_order_invoice';

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

    public function getInfoAttr($value)
    {
        if (!empty($value)) {
            return json_decode($value, true);
        }
        return [];
    }

    public function order()
    {
        return $this->hasOne(StoreOrder::class, 'id', 'order_id');
    }

    public function invoiceInfo()
    {
        return $this->hasOne(UserInvoice::class, 'id', 'invoice_id');
    }

    public function searchCategoryAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('category', $value);
        }
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value !== '' && !is_null($value)) $query->where('uid', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchOrderIdAttr($query, $value)
    {
        if ($value !== '' && !is_null($value)) $query->where('order_id', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchInvoiceIdAttr($query, $value)
    {
        if ($value !== '' && !is_null($value)) $query->where('invoice_id', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchHeaderTypeAttr($query, $value)
    {
        if ($value !== '' && !is_null($value)) $query->where('header_type', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '' && !is_null($value)) $query->where('type', $value);
    }

    public function searchInvoiceTimeAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereTime('invoice_time', 'between', $value);
            } else {
                $query->where('invoice_time', $value);
            }
        }
    }

    public function searchIsPayAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('is_pay', $value);
        }
    }
}
