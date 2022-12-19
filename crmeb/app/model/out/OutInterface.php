<?php

namespace app\model\out;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class OutInterface extends BaseModel
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
    protected $name = 'out_interface';

    public function searchTypeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('type', $value);
        }
    }
}