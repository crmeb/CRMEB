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

namespace app\dao\driver\sms;

use app\model\driver\sms\SmsDriver;
use crmeb\basic\BaseDao;

/**
 * 短信驱动数据访问层
 * Class SmsDriverDao
 * @package app\dao\driver\sms
 */
class SmsDriverDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SmsDriver::class;
    }

    /**
     * 获取所有已启用的短信驱动
     * @return \think\Collection
     */
    public function getAllEnabled()
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->select();
    }

    /**
     * 根据 driver_key 获取短信驱动
     * @param string $driverKey
     * @return SmsDriver|null
     */
    public function getByDriverKey(string $driverKey): ?SmsDriver
    {
        return $this->getModel()->where('driver_key', $driverKey)->find();
    }

    /**
     * 获取当前启用的驱动（短信为互斥模式，只允许一个启用）
     * @return SmsDriver|null
     */
    public function getActiveDriver(): ?SmsDriver
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->find();
    }

    /**
     * 禁用除指定ID外的所有驱动（互斥模式）
     * @param int $id 要保留的驱动ID
     * @return int
     */
    public function disableAllExcept(int $id): int
    {
        return $this->getModel()->where('id', '<>', $id)->update(['status' => 0]);
    }
}
