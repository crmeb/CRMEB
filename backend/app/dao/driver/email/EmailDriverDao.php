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

declare(strict_types=1);

namespace app\dao\driver\email;

use app\model\driver\email\EmailDriver;
use crmeb\basic\BaseDao;

/**
 * 邮件驱动数据访问层
 * Class EmailDriverDao
 * @package app\dao\driver\email
 */
class EmailDriverDao extends BaseDao
{
    protected function setModel(): string
    {
        return EmailDriver::class;
    }

    public function getAllEnabled()
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->select();
    }

    public function getByDriverKey(string $driverKey): ?EmailDriver
    {
        return $this->getModel()->where('driver_key', $driverKey)->find();
    }

    public function getActiveDriver(): ?EmailDriver
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->find();
    }

    public function disableAllExcept(int $id): int
    {
        return $this->getModel()->where('id', '<>', $id)->update(['status' => 0]);
    }
}