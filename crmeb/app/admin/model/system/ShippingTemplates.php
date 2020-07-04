<?php

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\admin\model\system
 */
class ShippingTemplates extends BaseModel
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
    protected $name = 'shipping_templates';

    use ModelTrait;

    public function getTypeAttr($value)
    {
        $status = [1 => '按件数', 2 => '按重量', 3 => '按体积'];
        return $status[$value];
    }

    public function getAppointAttr($value)
    {
        $status = [1 => '开启', 0 => '关闭'];
        return $status[$value];
    }

    public function getAddTimeAttr($value)
    {
        $value = date('Y-m-d H:i:s',$value);
        return $value;
    }

    /**
     * 运费模板列表
     * @param array $where
     * @return array
     */
    public static function getList($where = [])
    {
        $model = new self();
        if(isset($where['name'])&&$where['name']!='') $model = $model->where('name','like',"%$where[name]%");
        $data = $model->page($where['page'], $where['limit'])->order('sort','desc')->select()->toArray();
        $count = $model->count();
        return compact('count', 'data');
    }
}