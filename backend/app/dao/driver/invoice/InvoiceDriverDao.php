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

namespace app\dao\driver\invoice;

use app\model\driver\invoice\InvoiceDriver;
use crmeb\basic\BaseDao;

/**
 * 发票驱动数据访问层
 * Class InvoiceDriverDao
 * @package app\dao\driver\invoice
 */
class InvoiceDriverDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return InvoiceDriver::class;
    }

    /**
     * 获取所有已启用的发票驱动
     * @return \think\Collection
     */
    public function getAllEnabled()
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->select();
    }

    /**
     * 根据 driver_key 获取发票驱动
     * @param string $driverKey
     * @return InvoiceDriver|null
     */
    public function getByDriverKey(string $driverKey): ?InvoiceDriver
    {
        return $this->getModel()->where('driver_key', $driverKey)->find();
    }

    /**
     * 获取当前启用的驱动
     * @return InvoiceDriver|null
     */
    public function getActiveDriver(): ?InvoiceDriver
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->find();
    }

    /**
     * 禁用除指定ID外的所有驱动
     * @param int $id
     * @return int
     */
    public function disableAllExcept(int $id): int
    {
        return $this->getModel()->where('id', '<>', $id)->update(['status' => 0]);
    }
}
