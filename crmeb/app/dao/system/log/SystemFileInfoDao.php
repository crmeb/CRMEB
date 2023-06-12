<?php

namespace app\dao\system\log;

use app\dao\BaseDao;
use app\model\system\log\SystemFileInfo;

/**
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/04/07
 */
class SystemFileInfoDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemFileInfo::class;
    }
}