<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\agent;

use app\dao\agent\AgentLevelTaskRecordDao;
use app\services\BaseServices;


/**
 * Class AgentLevelTaskRecordServices
 * @package app\services\agent
 */
class AgentLevelTaskRecordServices extends BaseServices
{
    /**
     * AgentLevelTaskRecordServices constructor.
     * @param AgentLevelTaskRecordDao $dao
     */
    public function __construct(AgentLevelTaskRecordDao $dao)
    {
        $this->dao = $dao;
    }
}
