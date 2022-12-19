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

namespace app\dao\wechat;

use app\dao\BaseDao;
use app\model\other\Cache;

/**
 * Class WechatMenuDao
 * @package app\dao\wechat
 */
class WechatMenuDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    public function setModel(): string
    {
        return Cache::class;
    }
}
