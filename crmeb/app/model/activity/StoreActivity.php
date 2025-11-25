<?php

namespace app\model\activity;

use app\model\activity\seckill\StoreSeckill;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreActivity extends BaseModel
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
    protected $name = 'store_activity';

    public function seckill()
    {
        return $this->hasMany(StoreSeckill::class, 'activity_id', 'id')->where('is_show', 1)->where('is_del', 0);
    }
}
