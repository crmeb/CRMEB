<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------

namespace app\dao\user;

use app\model\user\UserWechatUser;
use crmeb\basic\BaseDao;

/**
 * 微信渠道身份映射数据访问层。
 */
class UserWechatIdentityDao extends BaseDao
{
    protected function setModel(): string
    {
        return UserWechatUser::class;
    }
}
