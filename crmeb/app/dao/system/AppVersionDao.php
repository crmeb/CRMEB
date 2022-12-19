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

declare (strict_types=1);

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\AppVersion;

/**
 * Class AppVersionDao
 * @package app\dao\system
 */
class AppVersionDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return AppVersion::class;
    }

    /**
     * 版本列表
     * @param $platform
     * @param $page
     * @param $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function versionList($platform, $page, $limit)
    {
        return $this->getModel()->when($platform != '', function ($query) use ($platform) {
            $query->where('platform', $platform);
        })->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time','desc')->select()->toArray();
    }
}
