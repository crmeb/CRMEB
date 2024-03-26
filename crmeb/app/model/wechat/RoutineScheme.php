<?php

namespace app\model\wechat;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class RoutineScheme extends BaseModel
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
    protected $name = 'routine_scheme';

    public function searchTitleAttr($query, $value)
    {
        if ($value !== '') $query->where('title', 'like', '%' . $value . '%');
    }
}