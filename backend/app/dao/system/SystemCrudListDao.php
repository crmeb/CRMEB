<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\dao\system;

use crmeb\basic\BaseDao;
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