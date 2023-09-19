<?php

namespace app\model\system;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/28
 */
class SystemSignReward extends BaseModel
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
    protected $name = 'system_sign_reward';

    /**
     * 类型搜索器
     * @param $query
     * @param $value
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/28
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('type', $value);
    }
}