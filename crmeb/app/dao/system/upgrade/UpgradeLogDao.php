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

namespace app\dao\system\upgrade;

use app\dao\BaseDao;
use app\model\system\upgrade\UpgradeLog;

/**
 * 升级记录dao
 * Class UpgradeLogDao
 * @package app\dao\system\upgrade
 */
class UpgradeLogDao extends BaseDao
{

    protected function setModel(): string
    {
        return UpgradeLog::class;
    }

    /**
     * 列表
     * @param array $where
     * @param array $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $field, int $page = 0, int $limit = 0): array
    {
        return $this->search()->field($field)->page($page, $limit)->select()->toArray();
    }
}