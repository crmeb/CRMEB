<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemSignReward;

/**
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/28
 */
class SystemSignRewardDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/28
     */
    protected function setModel(): string
    {
        return SystemSignReward::class;
    }
}