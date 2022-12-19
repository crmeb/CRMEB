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
declare (strict_types = 1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserTaskFinishDao;

/**
 *
 * Class UserTaskFinishServices
 * @package app\services\user
 */
class UserTaskFinishServices extends BaseServices
{

    /**
     * UserTaskFinishServices constructor.
     * @param UserTaskFinishDao $dao
     */
    public function __construct(UserTaskFinishDao $dao)
    {
        $this->dao = $dao;
    }

}
