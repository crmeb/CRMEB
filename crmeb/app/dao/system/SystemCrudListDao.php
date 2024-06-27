<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemCrudList;

/**
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/5/20
 */
class SystemCrudListDao extends BaseDao
{
    /**
     * @return string
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    protected function setModel(): string
    {
        return SystemCrudList::class;
    }
}