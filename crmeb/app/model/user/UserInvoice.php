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

namespace app\model\user;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class UserInvoice
 * @package app\model\live
 */
class UserInvoice extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'user_invoice';

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


    /**
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value !== '') $query->where('uid', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchHeaderTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('header_type', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('type', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsDefaultAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('is_default', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }

}
