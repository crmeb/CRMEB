<?php

namespace app\model\system;

use crmeb\basic\BaseModel;

/**
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/5/20
 */
class SystemCrudList extends BaseModel
{
    /**
     * @var string
     */
    protected $name = 'system_crud_list';

    /**
     * @var string
     */
    protected $pk = 'id';

    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }
}