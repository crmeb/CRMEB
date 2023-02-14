<?php

namespace app\model\system\timer;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class SystemTimer extends BaseModel
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
    protected $name = 'system_timer';
}