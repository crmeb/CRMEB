<?php

namespace app\model\agent;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class SpreadApply extends BaseModel
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
    protected $name = 'spread_apply';

    public function searchUidAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('uid', $value);
    }
}