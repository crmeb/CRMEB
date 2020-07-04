<?php

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\admin\model\system
 */
class SystemCity extends BaseModel
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
    protected $name = 'system_city';

    use ModelTrait;

    /**
     * 获取子集分类查询条件
     * @return \think\model\relation\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id','city_id')->order('id DESC');
    }

    public static function getList($where = [])
    {
        $list = self::withAttr('parent_id', function($value, $data) {
            if($value == 0){
                return '中国';
            }else{
                return self::where('city_id',$value)->value('name');
            }
        })->where('parent_id', $where['parent_id'])->select()->toArray();
        return $list;
    }
}