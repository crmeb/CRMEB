<?php

namespace app\models\system;

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
        return $this->hasMany(self::class, 'parent_id', 'city_id')->order('id ASC');
    }
}