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

namespace app\dao\system\log;

use crmeb\basic\BaseDao;
use app\model\system\log\SystemFileMd5;

class SystemFileMd5Dao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemFileMd5::class;
    }

    public function getList()
    {
        return $this->getModel()->select()->toArray();
    }
}
