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

namespace app\model\shipping;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\Model;

/**
 *  运费模板Model
 * Class ShippingTemplates
 * @package app\model\shipping
 */
class ShippingTemplates extends BaseModel
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
    protected $name = 'shipping_templates';

    /**
     * 类型获取器
     * @param $value
     * @return string
     */
    public function getTypeAttr($value)
    {
        $status = [1 => '按件数', 2 => '按重量', 3 => '按体积'];
        return $status[$value];
    }

    /**
     * 是否开启包邮获取器
     * @param $value
     * @return string
     */
    public function getAppointAttr($value)
    {
        $status = [1 => '开启', 0 => '关闭'];
        return $status[$value];
    }

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    public function getAddTimeAttr($value)
    {
        $value = date('Y-m-d H:i:s', $value);
        return $value;
    }

    /**
     * 运费地区一对多关联
     * @return \think\model\relation\HasMany
     */
    public function region()
    {
        return $this->hasMany(ShippingTemplatesRegion::class, 'temp_id', 'id');
    }

    /**
     * 包邮地区一对多关联
     * @return \think\model\relation\HasMany
     */
    public function free()
    {
        return $this->hasMany(ShippingTemplatesFree::class, 'temp_id', 'id');
    }

    /**
     * ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } else {
            $query->where('id', $value);
        }
    }

    /**
     * 模板名称搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchNameAttr($query, $value)
    {
        if ($value) {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

}
